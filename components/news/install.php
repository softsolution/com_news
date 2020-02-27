<?php
/* ****************************************************************************************** */
/* created by soft-solution.ru                                                                */
/* install.php of component news for InstantCMS 1.10.3                                        */
/* ****************************************************************************************** */
    function info_component_news(){
        $_component['title']        = 'Новости';
        $_component['description']  = 'Компонент Новости для InstantCMS';
        $_component['link']         = 'news';
        $_component['author']       = '<a href="http://soft-solution.ru">soft-solution.ru</a>';
        $_component['internal']     = '0';
        $_component['version']      = '1.1';
		
        $inCore = cmsCore::getInstance();
        $inCore->loadModel('news');
        
        $_component['config'] = cms_model_news::getDefaultConfig();
        
        return $_component;

    }

    function install_component_news(){

        $inCore     = cmsCore::getInstance();
        $inDB       = cmsDatabase::getInstance();
        $inConf     = cmsConfig::getInstance();

        include($_SERVER['DOCUMENT_ROOT'].'/includes/dbimport.inc.php');
        dbRunSQL($_SERVER['DOCUMENT_ROOT'].'/components/news/install.sql', $inConf->db_prefix);
        
        cmsCore::registerCommentsTarget('news', 'news', 'Новость', 'cms_news', 'вашей новости');

        cmsUser::registerGroupAccessType('news/add', 'Добавление новостей');
        cmsUser::registerGroupAccessType('news/delete', 'Удаление своих новостей');
        cmsUser::registerGroupAccessType('news/autoadd', 'Добавлять новости без модерации');
        
        if ($inCore->isComponentInstalled('billing')){
            cmsCore::loadClass('billing');
            if(!$inDB->rows_count('cms_billing_actions', "component='news' AND action='add_news'", 1)){
                cmsBilling::registerAction('news', array(
                    'name' => 'add_news',
                    'title' => 'Добавление новости')
                );
            }
        }

        return true;

    }


    function upgrade_component_news(){
        
        //$inCore     = cmsCore::getInstance();
        //$inDB       = cmsDatabase::getInstance();
        //$inConf     = cmsConfig::getInstance();
        
        return true;
        
    }
    
    function remove_component_news(){
	
        $inCore     = cmsCore::getInstance();
        $inDB       = cmsDatabase::getInstance();
        $inDB->query("DROP TABLE IF EXISTS cms_news");
        $inDB->query("DELETE FROM cms_comment_targets WHERE target='news' LIMIT 1");
		
    }
?>