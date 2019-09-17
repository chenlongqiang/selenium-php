<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/7
 * Time: 上午10:17
 */

namespace App\Model;
use App\Service\EasyDbService;
use App\Service\RedisService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class SpiderZhuBaJie
{
    protected $driver;
    protected $db;
    protected $redis;

    const SPIDER_STATUS_INIT = 0;
    const SPIDER_STATUS_SUCCESS = 1;
    const SPIDER_STATUS_FAIL = 2;

    public function __construct()
    {
        $this->redis = RedisService::getInstance();

        $this->db = EasyDbService::getInstance();
    }

    public function setDriver()
    {
        $options = new ChromeOptions();
//        $options->addArguments(["--headless", "--disable-gpu", "--no-sandbox"]);
        $options->addArguments(["--disable-gpu", "--no-sandbox"]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $capabilities->setPlatform("Mac");
        
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $this->driver = RemoteWebDriver::create($host, $capabilities, 5000); // start chrome with 5 second timeout
        $this->driver->manage()->timeouts()->implicitlyWait(5); // 隐性设置5秒等待，findElement 如果未找到会自动等待最多5秒
    }

    /*
    <div class="demand" title="切记！请您仔细看完需求，在和我联系。">
        <a data-linkid="tradingcenter-Demand_list-16059303" class="link-detail j-link-detail" data-taskid="16059303" href="//task.zbj.com/16059303/" target="_blank">
            <p class="d-title">
                <span title="需要网站定制开发" class="xq-title text-ellipisis">需要网站定制开发</span>
                <span class="mode-icons icons-topics">招标</span>
                <span class="d-tags text-ellipisis">
                    <span>匹配中</span>
                    <span>网站定制开发</span>
                    <span>全国</span>
                </span>
            </p>
            <p class="d-base">
                <b class="d-base-price">￥1</b>
                <span></span>
                <span>7 人已参与，剩余1个名额</span></p>
            <p class="d-des" title="切记！请您仔细看完需求，在和我联系。">切记！请您仔细看完需求，在和我联系。</p>
        </a>
    </div>
    */
    // 爬任务列表
    public function taskList($first_category, $page)
    {
        try {
            output_info("first_category: " . $first_category . ", page: " . $page . ", 开始抓取本页");
            $task_list_url = get_task_list_url($first_category, $page);
            $this->driver->get($task_list_url);
            $elements = $this->driver->findElements(WebDriverBy::cssSelector('.demand-list .demand'));
        } catch (Exception $e) {
            output_error("first_category: " . $first_category . ", findElements exception: " . $e->getMessage());
            return false;
        }
        
        foreach ($elements as $element) {
            try {
                $html = $element->getAttribute('innerHTML');
                $crawler = new Crawler($html);

                // === 字段抓取 start ===
                $taskid = $crawler->filter('a.link-detail')->attr('data-taskid');
                $redis_key = RedisService::getKey(
                    'already_fetch_taskid',
                    [$first_category, $taskid]
                );
                $is_exists = $this->redis->sismember($redis_key, $taskid);
                if ($is_exists === 1) {
                    output_warning("first_category: " . $first_category . ", taskid: " . $taskid . ", 该任务已抓取过 check for redis");
                    continue;
                }

                $url = $crawler->filter('a.link-detail')->attr('href'); // 需求地址
                $title = $crawler->filter('.d-title .xq-title')->attr('title'); // 需求标题
                $task_status = $crawler->filter('.d-title .mode-icons')->text(); // 需求状态

                $tags = []; // 需求标签
                $crawler->filter('.d-title .d-tags span')->each(function (Crawler $node, $i) use (&$tags) {
                    $tags[] = $node->text();
                });
                if (!empty($tags)) {
                    $tags = json_encode($tags);
                } else {
                    $tags = '';
                }

                $price_money = $crawler->filter('.d-base .d-base-price')->text(); // 金额
                $situation = $crawler->filter('.d-base span')->eq(1)->text(); // 参与情况
                // === 字段抓取 end ===

                $db_id = $this->db->insertReturnId('zhubajie_task', [
                    'taskid' => $taskid,
                    'url' => 'https:' . $url,
                    'title' => $title,
                    'task_status' => $task_status,
                    'tags' => $tags,
                    'price_money' => $price_money,
                    'situation' => $situation,
                    'first_category' => get_first_category_name($first_category),
                    'created_at' => now_datetime(),
                ]);
                output_success("first_category: " . $first_category . ", taskid: " . $taskid . " 入 db 成功, task 表 id: " . $db_id);
                $this->redis->sadd($redis_key, $taskid);
                continue;
            } catch (Exception $e) {
                if (preg_match('/SQLSTATE\[23000\]/', $e->getMessage())) {
                    $this->redis->sadd($redis_key, $taskid);
                    output_warning("first_category: " . $first_category . ", taskid: " . $taskid . ", 该任务已抓取过 check for db");
                    continue;
                }
                output_error("first_category: " . $first_category . ", taskid: " . $taskid . ", exception: " . $e->getMessage());
                continue;
            }
        }

        return true;
    }

    /*
    <div class="demand-content J-show-hide hide-more" style="max-height:196px;">
        <div class="order-attr">
            <i class="icon-font icon-yonghu"></i>客户
            <span>m**h</span>
            于 2019-09-06 18:02 发布该需求
        </div>
        <p class="up clearfix order-title J-order-title">
            <span class="fl title">需求标题</span>
            <span class="fr description">
                <span class="J-description-ordertitle">需要网站定制开发</span>
            </span>
        </p>
        <div class="clearfix J-order-price">
            <span class="fl title">预算金额</span>
            <p class="fr description">
                <span class="orange-color">3000<em>元</em></span>
                <span class="title trusteeship">已托管赏金</span>
                <span class="orange-color">0<em>元</em></span>
            </p>
        </div>
        <p class="clearfix copy-right-link hide" style="display: block;">
            <span class="fl title">需求推荐</span>
            <span class="fr description task-addtional">
                <label>
                    <a href="https://zt.ipr.zbj.com/trademark/sbcx/?_union_identify=21&amp;_union_uid=7223946&amp;_union_itemid=1615955" target="_blank" class="entrance-relative-href2" rel="nofollow">免费查询网站能否注册商标</a>
                </label>
            </span>
        </p>
        <p class="clearfix">
            <span class="fl title">需求描述</span>
            <span class="fr description">网站无障碍功能开发<br>参考网站地址：hainan.gov.cn/hainan/?blind=1</span>
        </p>
        <p class="clearfix">
            <span class="fl title">需求类型</span>
            <span class="fr description task-addtional">
                <label>网站定制开发</label>
            </span>
        </p>
        <p class="clearfix">
            <span class="fl title">补充需求</span>
            <span class="fr description">暂无</span>
        </p>
    </div>
    */
    // 爬任务详情
    public function taskDetail()
    {
        $wait_spider_queue = RedisService::getKey('wait_spider_queue');
        $tid = $this->redis->rpop($wait_spider_queue);
        if (is_null($tid)) {
            output_warning('没有任务详情页需要处理');
            return false;
        }
        $wait_spider_set = RedisService::getKey('wait_spider_set');
        $this->redis->srem($wait_spider_set, $tid);

        $task = $this->db->row(
            'select * from zhubajie_task where `id` = ?',
            $tid
        );
        if (empty($task)) {
            output_error('id: ' . $tid . ' 数据不存在');
            return true;
        }
        
        try {
            output_info("taskid: " . $task['taskid'] . " id: " . $task['id'] . ", 开始抓取本需求");
            $this->driver->get($task['url']);
            $element = $this->driver->findElement(WebDriverBy::cssSelector('.demand-main .demand-content'));
            $html = $element->getAttribute('innerHTML');
            $crawler = new Crawler($html);
            
            // === 字段抓取 start ===
            $public_at = $crawler->filter('.order-attr')->text(); // 需求发布时间
            
            $price_money = $crawler->filter('.J-order-price .description .orange-color')->eq(0)->text(); // 需要再处理
            $price_money = intval(str_replace(["\n", "元"], ["", ""], $price_money));
            if ($price_money === 0) {
                $price_money = '可议价';
            }
            
            $deposit_money = $crawler->filter('.J-order-price .description .orange-color')->eq(1)->text();
            $desc = $crawler->filter('.clearfix')->eq(3)->filter('span')->eq(1)->text();
            $second_category = $crawler->filter('.clearfix')->eq(4)->filter('span')->eq(1)->text();
            $desc_supplement = $crawler->filter('.clearfix')->eq(5)->filter('span')->eq(1)->text();
            // === 字段抓取 end ===

            $this->db->update('zhubajie_task',
                [
                    'public_at' => trim($public_at, "\n "),
                    'price_money' => $price_money,
                    'deposit_money' => trim($deposit_money, "\n "),
                    'desc' => trim($desc, "\n "),
                    'second_category' => trim($second_category, "\n "),
                    'desc_supplement' => trim($desc_supplement, "\n "),
                    'spider_status' => SpiderZhuBaJie::SPIDER_STATUS_SUCCESS,
                    'updated_at' => now_datetime(),
                ],
                ['taskid' => $task['taskid']]
            );
            output_success("taskid: " . $task['taskid'] . " 抓取成功");
            return true;
        } catch (Exception $e) {
            output_error("taskid: " . $task['taskid'] . " 抓取失败, exception: " . $e->getMessage());
            $this->db->update('zhubajie_task', 
                [
                    'spider_status' => SpiderZhuBaJie::SPIDER_STATUS_FAIL 
                ], 
                ['taskid' => $task['taskid']]
            );
            return true;
        }
    }

    public function getDbIdToRedis()
    {
        $tasks = $this->db->q(
            'select id from zhubajie_task where spider_status=? limit 10000',
            0
        );
        $wait_spider_queue = RedisService::getKey('wait_spider_queue');
        $wait_spider_set = RedisService::getKey('wait_spider_set');
        $cnt = 0;
        foreach ($tasks as $tid) {
            $resp = $this->redis->sadd($wait_spider_set, $tid['id']);
            if ($resp === 1) {
                $this->redis->lpush($wait_spider_queue, $tid['id']);
                $cnt ++;
            }
        }
        output_success('已经加入待爬取队列: ' . $cnt);
    }

}