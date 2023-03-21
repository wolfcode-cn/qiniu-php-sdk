<?php
namespace Qiniu;

use Qiniu\Http\Client;
use Qiniu\Http\Error;

class Region
{

    //源站上传域名
    public $srcUpHosts;
    //CDN加速上传域名
    public $cdnUpHosts;
    //资源管理域名
    public $rsHost;
    //资源列举域名
    public $rsfHost;
    //资源处理域名
    public $apiHost;
    //IOVIP域名
    public $iovipHost;
    // TTL
    public $ttl;

    //构造一个Region对象
    public function __construct(
        $srcUpHosts = array(),
        $cdnUpHosts = array(),
        $rsHost = "rs-z0.qiniuapi.com",
        $rsfHost = "rsf-z0.qiniuapi.com",
        $apiHost = "api.qiniuapi.com",
        $iovipHost = null,
        $ttl = null
    ) {

        $this->srcUpHosts = $srcUpHosts;
        $this->cdnUpHosts = $cdnUpHosts;
        $this->rsHost = $rsHost;
        $this->rsfHost = $rsfHost;
        $this->apiHost = $apiHost;
        $this->iovipHost = $iovipHost;
        $this->ttl = $ttl;
    }

    //华东机房
    public static function regionHuadong()
    {
        $regionHuadong = new Region(
            array("up.qiniup.com"),
            array('upload.qiniup.com'),
            'rs-z0.qiniuapi.com',
            'rsf-z0.qiniuapi.com',
            'api.qiniuapi.com',
            'iovip.qbox.me'
        );
        return $regionHuadong;
    }

    //华东机房内网上传
    public static function qvmRegionHuadong()
    {
        $qvmRegionHuadong = new Region(
            array("free-qvm-z0-xs.qiniup.com"),
            'rs-z0.qiniuapi.com',
            'rsf-z0.qiniuapi.com',
            'api.qiniuapi.com',
            'iovip.qbox.me'
        );
        return $qvmRegionHuadong;
    }

    //华北机房内网上传
    public static function qvmRegionHuabei()
    {
        $qvmRegionHuabei = new Region(
            array("free-qvm-z1-zz.qiniup.com"),
            "rs-z1.qiniuapi.com",
            "rsf-z1.qiniuapi.com",
            "api-z1.qiniuapi.com",
            "iovip-z1.qbox.me"
        );
        return $qvmRegionHuabei;
    }

    //华北机房
    public static function regionHuabei()
    {
        $regionHuabei = new Region(
            array('up-z1.qiniup.com'),
            array('upload-z1.qiniup.com'),
            "rs-z1.qiniuapi.com",
            "rsf-z1.qiniuapi.com",
            "api-z1.qiniuapi.com",
            "iovip-z1.qbox.me"
        );

        return $regionHuabei;
    }

    //华南机房
    public static function regionHuanan()
    {
        $regionHuanan = new Region(
            array('up-z2.qiniup.com'),
            array('upload-z2.qiniup.com'),
            "rs-z2.qiniuapi.com",
            "rsf-z2.qiniuapi.com",
            "api-z2.qiniuapi.com",
            "iovip-z2.qbox.me"
        );
        return $regionHuanan;
    }

    //华东2 机房
    public static function regionHuadong2()
    {
        return new Region(
            array('up-cn-east-2.qiniup.com'),
            array('upload-cn-east-2.qiniup.com'),
            "rs-cn-east-2.qiniuapi.com",
            "rsf-cn-east-2.qiniuapi.com",
            "api-cn-east-2.qiniuapi.com",
            "iovip-cn-east-2.qiniuio.com"
        );
    }

    //北美机房
    public static function regionNorthAmerica()
    {
        //北美机房
        $regionNorthAmerica = new Region(
            array('up-na0.qiniup.com'),
            array('upload-na0.qiniup.com'),
            "rs-na0.qiniuapi.com",
            "rsf-na0.qiniuapi.com",
            "api-na0.qiniuapi.com",
            "iovip-na0.qbox.me"
        );
        return $regionNorthAmerica;
    }

    //新加坡机房
    public static function regionSingapore()
    {
        //新加坡机房
        $regionSingapore = new Region(
            array('up-as0.qiniup.com'),
            array('upload-as0.qiniup.com'),
            "rs-as0.qiniuapi.com",
            "rsf-as0.qiniuapi.com",
            "api-as0.qiniuapi.com",
            "iovip-as0.qbox.me"
        );
        return $regionSingapore;
    }

    //首尔
    public static function regionSeoul()
    {
        //首尔
        return new Region(
            array('up-ap-northeast-1.qiniup.com'),
            array('upload-ap-northeast-1.qiniup.com'),
            "rs-ap-northeast-1.qiniuapi.com",
            "rsf-ap-northeast-1.qiniuapi.com",
            "api-ap-northeast-1.qiniuapi.com",
            "iovip-ap-northeast-1.qiniuio.com"
        );
    }

    /*
     * GET /v2/query?ak=<ak>&bucket=<bucket>
     **/
    public static function queryRegion($ak, $bucket, $ucHost = null)
    {
        $Region = new Region();
        if (!$ucHost) {
            $ucHost = "https://" . Config::UC_HOST;
        }
        $url = $ucHost . '/v2/query' . "?ak=$ak&bucket=$bucket";
        $ret = Client::Get($url);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        //parse Region;

        $iovipHost = $r['io']['src']['main'][0];
        $Region->iovipHost = $iovipHost;
        $accMain = $r['up']['acc']['main'][0];
        array_push($Region->cdnUpHosts, $accMain);
        if (isset($r['up']['acc']['backup'])) {
            foreach ($r['up']['acc']['backup'] as $key => $value) {
                array_push($Region->cdnUpHosts, $value);
            }
        }
        $srcMain = $r['up']['src']['main'][0];
        array_push($Region->srcUpHosts, $srcMain);
        if (isset($r['up']['src']['backup'])) {
            foreach ($r['up']['src']['backup'] as $key => $value) {
                array_push($Region->srcUpHosts, $value);
            }
        }

        //set specific hosts
        if (isset($r['rs']['acc']['main']) && count($r['rs']['acc']['main']) > 0) {
            $Region->rsHost = $r['rs']['acc']['main'][0];
        } else {
            $Region->rsHost = Config::RS_HOST;
        }
        if (isset($r['rsf']['acc']['main']) && count($r['rsf']['acc']['main']) > 0) {
            $Region->rsfHost = $r['rsf']['acc']['main'][0];
        } else {
            $Region->rsfHost = Config::RSF_HOST;
        }
        if (isset($r['api']['acc']['main']) && count($r['api']['acc']['main']) > 0) {
            $Region->apiHost = $r['api']['acc']['main'][0];
        } else {
            $Region->apiHost = Config::API_HOST;
        }

        // set ttl
        $Region->ttl = $r['ttl'];

        return $Region;
    }
}
