<?php

    define('PATH', $_SERVER['DOCUMENT_ROOT']);
    include(PATH.'/core/ajax/ajax_core.php');

    cmsCore::loadLanguage('modules/mod_news');

    $smarty = $inCore->initSmarty();

    $page       = cmsCore::request('page', 'int', 1);	
    $module_id	= cmsCore::request('module_id', 'int', '');

    if(!$page || !$module_id) { cmsCore::halt(); }

    $cfg = $inCore->loadModuleConfig($module_id);
    
    if (!isset($cfg['newscount'])) { $cfg['newscount'] = 5; }
    if (!isset($cfg['showdesc'])) { $cfg['showdesc'] = 1; }
    if (!isset($cfg['showdate'])) { $cfg['showdate'] = 1; }
    if (!isset($cfg['showcom'])) { $cfg['showcom'] = 1; }
    if (!isset($cfg['showrss'])) { $cfg['showrss'] = 0; }
    if (!isset($cfg['is_pag'])) { $cfg['is_pag'] = 0; }
        
    // Если пагинация отключена, выходим
    if (!$cfg['is_pag']) { cmsCore::halt(); }

    $inCore->loadModel('news');
    $model = new cms_model_news();

    $inDB->where("con.showlatest = 1");

    $total = $model->getArticlesCount();

    $inDB->orderBy('con.pubdate', 'DESC');
    $inDB->limitPage($page, $cfg['newscount']);

    $content_list = $model->getArticlesList();
    if(!$content_list) { cmsCore::halt(); }

    $smarty = $inCore->initSmarty('modules', 'mod_news.tpl');			
    $smarty->assign('articles', $content_list);
    $smarty->assign('pagebar_module', cmsPage::getPagebar($total, $page, $cfg['newscount'], 'javascript:newsPage(%page%, '.$module_id.')'));
    $smarty->assign('is_ajax', true);
    $smarty->assign('module_id', $module_id);
    $smarty->assign('cfg', $cfg);
    $smarty->display('mod_news.tpl');		

?>
