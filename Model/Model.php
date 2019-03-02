<?php

namespace Model;

use Library\MongoQB\Exception;
use Ypf\Core\Model as BaseModel;

class Model extends BaseModel
{
    const FORM_TYPE_NUMBER   = 'number';    //MySQL字段对应表单类型：数字类型
    const FORM_TYPE_SELECT   = 'select';    //MySQL字段对应表单类型：枚举型
    const FORM_TYPE_STRING   = 'string';    //MySQL字段对应表单类型：字符类型
    const FORM_TYPE_TEXT     = 'text';      //MySQL字段对应表单类型：文本类型
    const FORM_TYPE_DATE     = 'date';      //MySQL字段对应表单类型：日期类型
    const FORM_TYPE_DEFAULT  = 'unknown';   //MySQL字段对应表单类型：无类型
    const FORM_TYPE_FILE     = 'file';      //MySQL字段对应表单类型：文件类型
    const FORM_TYPE_CHECKBOX = 'checkbox';  //MySQL字段对应表单类型：多选类型

    public static $db_conf = null;
    public static $instance_list = [];
    public $db_name = 'db';
    public $table_name = '';
    protected $db_instance = null;    //数据对象实例
    protected $db_conf_name = 'cp';
    protected $methods = [        // 链操作方法列表
        'strict',
        'order',
        'alias',
        'having',
        'group',
        'lock',
        'distinct',
        'auto',
        'filter',
        'validate',
        'result',
        'token',
        'index',
        'force'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据条件查询数据列表
     * @param array $where
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @param array $fields
     * @param string $group
     * @param string $sortKey
     * @return mixed
     */
    public function getListByWhere(
        $where = array(),
        $offset = 0,
        $limit = 999999,
        $order = array(),
        $fields = array(),
        $group = '',
        $sortKey = ''
    ) {
        $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
        $offset    = intval($offset);
        $limit     = intval($limit);
        $sql_limit = "{$offset},{$limit}";
        $group     = trim($group);

        if (is_array($order)) {
            $arr_order = array();
            foreach ($order as $key => $val) {
                $arr_order[] = " {$key} {$val} ";
            }
            $sql_order = implode(' , ', $arr_order);
        } else {
            $sql_order = $order;
        }
        if (is_array($fields)) {
            $sql_field = implode(' , ', $fields);
        } else {
            $sql_field = $fields;
        }
        if ($sql_field) {
            if (empty($order)) {
                $list = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->limit($sql_limit)->field($sql_field)->group($group)->select();
            } else {
                $list = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->limit($sql_limit)->order($sql_order)->field($sql_field)->group($group)->select();
            }
        } else {
            if (empty($order)) {
                $list = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->limit($sql_limit)->group($group)->select();
            } else {
                $list = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->limit($sql_limit)->order($sql_order)->group($group)->select();
            }
        }
        self::_sortData($list, $sortKey);
        return $list;
    }


    /**
     * 根据条件查询一条记录
     * @param array $where
     * @param array $fields
     * @param string $group
     * @return mixed
     */
    public function getOneByWhere($where = array(), $fields = array(), $group = '')
    {
        $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
        $group     = trim($group);
        if (is_array($fields)) {
            $sql_field = implode(' , ', $fields);
        } else {
            $sql_field = $fields;
        }
        if ($sql_field) {
            $data = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->field($sql_field)->group($group)->fetch();
        } else {
            $data = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->group($group)->fetch();
        }

        return $data;
    }

    /**
     * 获取单个字段的值
     * @param array $where
     * @param string $field
     * @param string $group
     * @return bool
     */
    public function getOneFieldByWhere($where = array(), $field = '', $group = '')
    {
        $res = false;
        if ($field) {
            $info = $this->getOneByWhere($where, '', $group);
            isset($info[$field]) && $res = $info[$field];
        }
        return $res;
    }

    /**
     * 根据条件查询数据列表总数
     * @param array $where
     * @param string $group
     * @return mixed
     */
    public function getListCountByWhere($where = array(), $group = '')
    {
        $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
        $group     = trim($group);
        return $this->{$this->db_name}->table($this->table_name)->where($sql_where)->group($group)->count();

    }

    /**
     * 以主键更新数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateById($id = 0, $data = array())
    {
        $res = false;
        if ($id && is_array($data) && !empty($data)) {
            if (in_array('id', array_keys($data))) {
                unset($data['id']);
            }
            $res = $this->{$this->db_name}->table($this->table_name)->where(" id = {$id} ")->update($data);
        }
        return $res;
    }

    /**
     * 根据where条件更新数据
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function updateByWhere($where = array(), $data = array())
    {
        $res = false;
        if (is_array($data) && !empty($data)) {
            $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
            if (in_array('id', array_keys($data))) {
                unset($data['id']);
            }
            $res = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->update($data);
        }
        return $res;
    }

    public function deleteByWhere($where = array())
    {
        $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
        $res       = $this->{$this->db_name}->table($this->table_name)->where($sql_where)->delete();
        return $res;
    }

    /**
     * 复杂更新，直接指向SQL语句
     * @param string $update_sql
     * @param array $where
     * @return bool
     */
    public function updateFromSqlByWhere($update_sql = '', $where = array())
    {
        $ret       = false;
        $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
        if ($sql_where && $update_sql) {
            $query_sql = "{$update_sql} WHERE {$sql_where}";
            $res       = $this->{$this->db_name}->query($query_sql);
            $res !== false && $ret = true;
        }
        return $ret;
    }

    /**
     * 根据条件查询数据列表
     * @param array $where
     * @param array $fields
     * @return mixed
     */
    public function find($where = array(), $fields = array())
    {
        $sql_where = is_array($where) ? $this->{$this->db_name}->parseWhere($where) : $where;
        if (is_array($fields) && !empty($fields)) {
            $sql_field = implode(' , ', $fields);
        } else {
            $sql_field = $fields ? $fields : '*';
        }
        return $this->{$this->db_name}->table($this->table_name)->where($sql_where)->field($sql_field)->fetch();

    }

    public function add($data = array())
    {
        $res = false;
        if (is_array($data) && !empty($data)) {
            $res = $this->{$this->db_name}->table($this->table_name)->insert($data);
        }
        return $res;
    }

    /**
     * @node_name 批量新增
     * @param array $data
     * @return bool
     */
    public function batchAdd($data = [])
    {
        $res = false;
        if (is_array($data) && !empty($data)) {
            $res = $this->{$this->db_name}->table($this->table_name)->batchInsert($data);
        }
        return $res;
    }

    /**
     * @param array $data
     * @return bool
     * @node_name 插入单个或多个记录
     * @link
     * @desc
     */
    public function create(array $data)
    {
        $res = false;
        if (is_array($data) && !empty($data)) {
            $res = $this->{$this->db_name}->table($this->table_name)->create($data);
        }
        return $res;
    }


    public function selectBySql($sql = '')
    {
        return $this->{$this->db_name}->table($this->table_name)->select($sql);
    }

    public function getLastSql()
    {
        return $this->{$this->db_name}->table($this->table_name)->getLastSql();
    }

    public function getTableFields($table_name = '')
    {
        empty($table_name) && $table_name = $this->table_name;
        $sql = " SHOW FULL COLUMNS FROM {$table_name} ";
        try {
            $list   = $this->selectBySql($sql);
            $fields = [];
            if (!empty($list)) {
                foreach ($list as $field) {
                    $field              = array_change_key_case($field, CASE_LOWER);
                    $mysql_field        = $field['type'];
                    $pattern            = '/\([^()]*\)/';
                    $field['type']      = preg_replace($pattern, '', $field['type']);
                    $field['form_type'] = $this->getFormType($field['type']);
                    $matches            = $new_values = [];
                    preg_match($pattern, $mysql_field, $matches);
                    $default_value = explode(',', str_replace(['(', ')', '\''], '', array_pop($matches)));
                    foreach ($default_value as $key => $val) {
                        if (!empty($val)) {
                            array_push($new_values, "{$val}|{$val}");
                        }
                    }
                    $field['default_value'] = implode(',', $new_values);
                    array_push($fields, $field);
                }
            }
            return $fields;
        } catch (Exception $e) {
            return [];
        }

    }

    /**
     * @node_name 查询字段类型对应的表单类型
     * @param string $field_type
     * @return string
     */
    private function getFormType($field_type = '')
    {

        if (false !== stripos($field_type, 'tinyint')
            || false !== stripos($field_type, 'enum')
        ) {
            return self::FORM_TYPE_SELECT;
        }

        if (false !== stripos($field_type, 'int')
            || false !== stripos($field_type, 'float')
            || false !== stripos($field_type, 'double')
            || false !== stripos($field_type, 'decimal')
        ) {
            return self::FORM_TYPE_NUMBER;
        }

        if (false !== stripos($field_type, 'char')
            || false !== stripos($field_type, 'tinytext')
            || false !== stripos($field_type, 'mediumblob')
            || false !== stripos($field_type, 'mediumtext')
        ) {
            return self::FORM_TYPE_STRING;
        }

        if (false !== stripos($field_type, 'blob')
            || false !== stripos($field_type, 'text')
            || false !== stripos($field_type, 'binary')
        ) {
            return self::FORM_TYPE_TEXT;
        }

        if (false !== stripos($field_type, 'date')
            || false !== stripos($field_type, 'time')
            || false !== stripos($field_type, 'year')
        ) {
            return self::FORM_TYPE_DATE;
        }

        return self::FORM_TYPE_DEFAULT;

    }


    /**
     * @param string $db_name
     * @return array|bool
     * @node_name 验证数据库中是否有此表
     * @link
     * @desc
     */
    public function existsTableNameByDB($db_name = "yundun_cp")
    {
        empty($table_name) && $table_name = $this->table_name;
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema='{$db_name}' AND table_type='base table' ";
        try {

            $list   = $this->selectBySql($sql);
            $tables = [];
            if (!empty($list)) {
                foreach ($list as $vo) {
                    $tables[] = $vo['table_name'];
                }

                return $tables;
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $data
     * @param string $key
     * @node_name 指定数组某元素为数组的键值
     * @link
     * @desc
     */
    private function _sortData(&$data = [], $key = '')
    {
        $res = [];
        if (!empty($data) && is_array($data) && !empty($key)) {
            foreach ($data as $d) {
                $res[$d[$key]] = $d;
            }
            $data = $res;
        }
    }

}
