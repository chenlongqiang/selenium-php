# 使用 php 配合 selenium 进行数据采集，手摸手教学

## tips！
`本项目以采集 猪八戒任务 为例仅用于学习交流，采集前请阅读 robots.txt 协议`

`禁止用于非法行为，后果自负`

## 运行环境及依赖说明

运行环境：php7.1，redis-4.0，mysql-5.6  
依赖：java，chrome，chromedriver，selenium

## 依赖下载，已在百度网盘帮你准备好
链接：https://pan.baidu.com/s/1gbSckvixLMbW5JB3eaY6dQ
提取码：29qb

## 如不使用网盘，依赖包下载链接如下 

### 依赖1: java jdk8 download
https://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html

### 依赖2: chrome download, my version: 76.0.3809.132
https://www.chromedownloads.net/

### 依赖3: chromedriver download, my version: 72.0.3626.69
https://chromedriver.storage.googleapis.com/index.html?path=72.0.3626.69/  
download other version:  
https://chromedriver.storage.googleapis.com/index.html  

### 依赖4: selenium server download
https://www.seleniumhq.org/download/

## ps windows 如何设置环境变量
https://www.java.com/zh_CN/download/help/path.xml

## 使用流程
- 安装好运行环境及依赖，并启动  
- 创建数据库，导入数据表 sql  
mysql -u username -ppassword -e "create database selenium_php character set utf8 collate utf8_general_ci"
mysql -u username -ppassword selenium_php < zhubajie.sql  
- 配置 .env，redis mysql  
- cd selenium-php  
- java -jar selenium-server-standalone-3.141.59.jar  
- 采集列表页（爬取页码当前写死2~5页）php scripts/zhubajie/spider_list.php >> ./log/spider_list.log 2>&1  
- 列表页采集完成后，将任务丢进 redis 队列（方便详情页多进程采集）php scripts/zhuabajie/get_db_id_to_redis.php  
- 采集详情页 php scripts/zhuabajie/spider_detail.php >> ./log/spider_detail.log 2>&1  

