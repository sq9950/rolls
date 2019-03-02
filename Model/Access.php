<?php

namespace Model;

use Model\CommonModel;

class Access extends CommonModel
{
    public function __construct()
    {
        parent::__construct();

        $this->table_name = 'rbac_access';
    }

    public function get_role_node_ids($role_id = 0)
    {
        $ids  = [];
        $rows = $this->getListByWhere(['role_id' => $role_id]);

        if (is_array($rows) && !empty($rows)) {
            foreach ($rows as $row) {
                $ids[] = $row['node_id'];
            }
        }

        return $ids;
    }
}
