<?php

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function rss_news($item_id, $cfg){

    if(!cmsCore::getInstance()->isComponentEnable('news')) { return false; }

	$inDB = cmsDatabase::getInstance();

	global $_LANG;

	cmsCore::loadModel('news');
	$model = new cms_model_news();

	$channel = array();
	$items   = array();

        $channel['title'] = $_LANG['NEW_MATERIALS'];
        $channel['description'] = $_LANG['LAST_ARTICLES_NEWS'];
        $channel['link'] = HOST.'/news';

	$inDB->where("con.showlatest = 1");

	$inDB->orderBy('con.pubdate', 'DESC');
	$inDB->limit($cfg['maxitems']);

	$content = $model->getArticlesList();

	if($content){
            foreach($content as $con){
                $con['link']     = HOST.$con['url'];
                $con['comments'] = $con['link'].'#c';

                if($con['image']){
                    $con['size']  = round(filesize(PATH.'/images/news/small/'.$con['image']));
                    $con['image'] = HOST . '/images/news/small/'.$con['image'];
                }

                $items[] = $con;
            }
	}

	return array('channel' => $channel, 'items' => $items);

}

?>