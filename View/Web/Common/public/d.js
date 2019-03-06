const fs = require('fs');
const path = require('path');
const makeDir = require('make-dir');
const async = require('async');
const request = require('request');

const imageLinks = [
  'http://localhost:81/content/dam/rollsroyce-website/Cruising%20on%20the%20Riviera/01.Hero_HEIGHT-1536.jpg.rr.1198.LOW.jpg',
  'http://localhost:81/content/dam/rollsroyce-website/Cruising%20on%20the%20Riviera/01.Hero_HEIGHT-1536.jpg.rr.1536.LOW.jpg',
  'http://localhost:81/content/dam/rollsroyce-website/Cruising%20on%20the%20Riviera/01.Hero_HEIGHT-1536.jpg.rr.2048.LOW.jpg',
  'http://localhost:81/content/dam/rollsroyce-website/Cruising%20on%20the%20Riviera/01.Hero_HEIGHT-1536.jpg.rr.1366.MED.jpg',
  'http://localhost:81/content/dam/rollsroyce-website/Cruising%20on%20the%20Riviera/01.Hero_HEIGHT-1536.jpg.rr.1920.MED.jpg',
];

const index = 1;

const downloadImage = function (src, dest, callback) {
  request.head(src, (err, res, body) => {
    console.log('111');
    if (src) {
      console.log('222');
      request(src)
        .pipe(fs.createWriteStream(dest))
        .on('close', () => {
          callback(null, dest);
        });
    }
  });
};

async.mapSeries(
  imageLinks,
  (item, callback) => {
    const onlinePath = item.replace(
      'http://localhost:81',
      'https://www.rolls-roycemotorcars.com.cn',
    );
    const localPath = item.replace('http://localhost:81', '/Users/lishiqiang/app/rolls/Public/Web');
    setTimeout(() => {
      const dirPath = localPath.slice(0, localPath.lastIndexOf('/') + 1);
      console.log('---dirPath', dirPath);
      makeDir(dirPath).then(() => {
        console.log('---onlinePath', onlinePath);
        console.log('---localPath', localPath);
        downloadImage(onlinePath, localPath, () => {
          console.log('success');
        });
      });

      callback(null, item);
    }, 100);
  },
  (err, results) => {},
);
