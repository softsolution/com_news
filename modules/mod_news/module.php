<?php

function mod_news($module_id){

	//выходим если это не страница новости (показываем модуль только для страницы новости, для остальных - выходим)
	if(!preg_match('/^\/news\/(.+).html$/i', $_SERVER['REQUEST_URI'])){ return false; }

    $inCore = cmsCore::getInstance();
    $inDB   = cmsDatabase::getInstance();

    $inCore->loadModel('news');
    $model = new cms_model_news();

    $cfg = $inCore->loadModuleConfig($module_id);

    if (!isset($cfg['newscount'])) { $cfg['newscount'] = 5; }
    if (!isset($cfg['showdesc'])) { $cfg['showdesc'] = 1; }
    if (!isset($cfg['showdate'])) { $cfg['showdate'] = 1; }
    if (!isset($cfg['showcom'])) { $cfg['showcom'] = 1; }
    if (!isset($cfg['showrss'])) { $cfg['showrss'] = 0; }
    if (!isset($cfg['is_pag'])) { $cfg['is_pag'] = 0; }

    $inDB->where("con.showlatest = 1");

    if ($cfg['is_pag']){
        $total = $model->getArticlesCount();
    }

    $inDB->orderBy('con.pubdate', 'DESC');
    $inDB->limitPage(1, $cfg['newscount']);

    $content_list = $model->getArticlesList();
    if(!$content_list) { return false; }

    $smarty = $inCore->initSmarty('modules', 'mod_news.tpl');
    $smarty->assign('articles', $content_list);
    if ($cfg['is_pag']) {
        $smarty->assign('pagebar_module', cmsPage::getPagebar($total, 1, $cfg['newscount'], 'javascript:newsPage(%page%, '.$module_id.')'));
    }
    $smarty->assign('is_ajax', false);
    $smarty->assign('module_id', $module_id);
    $smarty->assign('cfg', $cfg);
    $smarty->display('mod_news.tpl');

    return true;

}
?>