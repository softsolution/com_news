<?php
/* ****************************************************************************************** */
/* created by soft-solution.ru                                                                */
/* frontend.php of component news for InstantCMS 1.10.3                                       */
/* ****************************************************************************************** */

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

function news(){

    $inCore = cmsCore::getInstance();
    $inPage = cmsPage::getInstance();
    $inDB   = cmsDatabase::getInstance();
    $inUser = cmsUser::getInstance();
    
    cmsCore::loadModel('news');
    $model = new cms_model_news();

    define('IS_BILLING', $inCore->isComponentInstalled('billing'));
    if (IS_BILLING) { cmsCore::loadClass('billing'); }

    global $_LANG;

    $id = cmsCore::request('id', 'int', 0);
    $do = $inCore->do;

    $seolink = cmsCore::strClear(urldecode(cmsCore::request('seolink', 'html', '')));

    $page = cmsCore::request('page', 'int', 1);
    
    $cfg = $model->config;

/* ========================================================================== */
/* ======================== ГЛАВНАЯ СТРАНИЦА НОВОСТЕЙ ======================= */
/* ========================================================================== */
    
if ($do=='view'){

    $inPage->setTitle($_LANG['CATALOG_ARTICLES']);
    $pagetitle = $_LANG['CATALOG_ARTICLES'];
    
    $inPage->addPathway($_LANG['CATALOG_ARTICLES']);
    
    // Мета теги KEYWORDS и DESCRIPTION
    if ($cfg['keywords']){ $inPage->setKeywords($cfg['keywords']); }
    if ($cfg['metadesc']){ $inPage->setDescription($cfg['metadesc']);	}
    
    // Редактор/администратор
    $is_editor = (cmsUser::isUserCan('news/autoadd') || $inUser->is_admin);
    
    // Общее количество статей
    $total = $model->getArticlesCount($is_editor);

    // Сортировка и разбивка на страницы
    $inDB->orderBy($cfg['orderby'], $cfg['orderto']);
    $inDB->limitPage($page, $cfg['perpage']);

    // Получаем статьи
    $content_list = $total ? $model->getArticlesList(!$is_editor) : array();
    $inDB->resetConditions();

    $pagebar  = cmsPage::getPagebar($total, $page, $cfg['perpage'], '/news/page-%page%');

    cmsPage::initTemplate('components', 'com_news_view.tpl')->
    assign('cfg', $cfg)->
    assign('pagetitle', $pagetitle)->
    assign('articles', $content_list)->
    assign('pagebar', $pagebar)->
    display('com_news_view.tpl');

}

/* ========================================================================== */
/* ======================== READ ARTICLE / ПРОСМОТР НОВОСТИ ================= */
/* ========================================================================== */
if ($do=='read'){

    // Получаем новость
    $article = $model->getArticle($seolink);
    if (!$article) { cmsCore::error404(); }

    //$article = cmsCore::callEvent('GET_ARTICLE', $article);

    if ($inUser->id) {
        $is_admin      = $inUser->is_admin;
        $is_author     = $inUser->id == $article['user_id'];
        $is_author_del = cmsUser::isUserCan('news/delete');
        $is_editor     = cmsUser::isUserCan('news/autoadd');
    }

    // если статья не опубликована или дата публикации позже, 404
    if ((!$article['published'] || strtotime($article['pubdate']) > time()) && !$is_admin && !$is_editor && !$is_author) { cmsCore::error404(); }

    // увеличиваем кол-во просмотров
    if(@!$is_author){
        $inDB->setFlag('cms_news', $article['id'], 'hits', $article['hits']+1);
    }

    // Картинка статьи
    $article['image'] = (file_exists(PATH.'/images/news/medium/news'.$article['id'].'.jpg') ? 'news'.$article['id'].'.jpg' : '');
    
    // Заголовок страницы
    $article['pagetitle'] = $article['pagetitle'] ? $article['pagetitle'] : $article['title'];
    
    // Тело статьи в зависимости от настроек
    $article['content'] = $cfg['readdesc'] ? $article['description'].$article['content'] : $article['content'];
    
    // Дата публикации
    $article['pubdate'] = cmsCore::dateformat($article['pubdate']);
    
    // Шаблон статьи
    $article['tpl'] = $article['tpl'] ? $article['tpl'] : 'com_news_read.tpl';

    $inPage->setTitle($article['pagetitle']);
    $inPage->addPathway($_LANG['CATALOG_ARTICLES'], "/news");

    $inPage->addPathway($article['title']);

    // Мета теги KEYWORDS и DESCRIPTION
    if ($article['meta_keys']){
            $inPage->setKeywords($article['meta_keys']);
	} else {
        if (mb_strlen($article['content'])>30){
            $inPage->setKeywords(cmsCore::getKeywords(cmsCore::strClear($article['content'])));
        }
    }
    if (mb_strlen($article['meta_desc'])){
            $inPage->setDescription($article['meta_desc']);
    }

    // Выполняем фильтры
    $article['content'] = cmsCore::processFilters($article['content']);

    cmsPage::initTemplate('components', $article['tpl'])->
    assign('article', $article)->
    assign('cfg', $cfg)->
    assign('is_admin', $is_admin)->
    assign('is_editor', $is_editor)->
    assign('is_author', $is_author)->
    assign('is_author_del', $is_author_del)->
    assign('tagbar', cmsTagBar('news', $article['id']))->
    display($article['tpl']);

    // Комментарии статьи
    if($article['published'] && $article['comments'] && $inCore->isComponentInstalled('comments') && $cfg['show_comments']){
        cmsCore::includeComments();
        comments('news', $article['id']);
    }

}

/* ==================================================================================================== */
/* ======================== ADD NEWS / ДОБАВЛЕНИЕ НОВОСТИ С ФРОНТА ==================================== */
/* ==================================================================================================== */
if ($do=='addarticle' || $do=='editarticle'){

    $is_add      = cmsUser::isUserCan('news/add');
    $is_auto_add = cmsUser::isUserCan('news/autoadd');

    if (!$is_add && !$is_auto_add){ cmsCore::error404(); }

    // Для редактирования получаем новость и проверяем доступ
    if ($do=='editarticle'){
        // Получаем статью
        $item = $model->getArticle($id);
        if (!$item) { cmsCore::error404(); }

        // доступ к редактированию админам, авторам и редакторам
        if(!$inUser->is_admin && ($item['user_id'] != $inUser->id) && !(cmsUser::isUserCan('news/autoadd'))){
            cmsCore::error404();
        }
    }

    // Для добавления проверяем не вводили ли мы данные ранее
    if ($do=='addarticle'){
        $item = cmsUser::sessionGet('article');
        if ($item) { cmsUser::sessionDel('article'); }
    }

    // не было запроса на сохранение, показываем форму
    if (!cmsCore::inRequest('add_mod')){

        // Если добавляем новость
        if ($do=='addarticle'){

            $pagetitle = $_LANG['ADD_ARTICLE'];

            $inPage->setTitle($pagetitle);
            

            $inPage->addPathway($_LANG['CATALOG_ARTICLES'], '/news');
            $inPage->addPathway($_LANG['MY_ARTICLES'], '/news/my.html');
            $inPage->addPathway($pagetitle);

            // поддержка биллинга
            if (IS_BILLING){
                cmsBilling::checkBalance('news', 'add_news');
            }
        }

        // Если редактируем новость
        if ($do=='editarticle'){

            $pagetitle = $_LANG['EDIT_ARTICLE'];

            $inPage->setTitle($pagetitle);
            
            $inPage->addPathway($_LANG['CATALOG_ARTICLES'], '/news');
            $inPage->addPathway($_LANG['MY_ARTICLES'], '/news/my.html');
            $inPage->addPathway($pagetitle);

            $item['tags']  = cmsTagLine('news', $item['id'], false);
            $item['image'] = (file_exists(PATH.'/images/news/small/news'.$item['id'].'.jpg') ? 'news'.$item['id'].'.jpg' : '');

            if (!$is_auto_add){
                cmsCore::addSessionMessage($_LANG['ATTENTION'].': '.$_LANG['EDIT_ARTICLE_PREMODER'], 'info');
            }

        }

        $inPage->initAutocomplete();
        $autocomplete_js = $inPage->getAutocompleteJS('tagsearch', 'tags');

        $item = cmsCore::callEvent('PRE_EDIT_ARTICLE', (@$item ? $item : array()));
        
        cmsPage::initTemplate('components', 'com_news_edit.tpl')->
        assign('mod', $item)->
        assign('do', $do)->
        assign('cfg', $cfg)->
        assign('pagetitle', $pagetitle)->
        assign('is_admin', $inUser->is_admin)->
        assign('is_billing', IS_BILLING)->
        assign('autocomplete_js', $autocomplete_js)->
        display('com_news_edit.tpl');

    }

    // Пришел запрос на сохранение статьи
    if (cmsCore::inRequest('add_mod')){

        $errors = false;

        $article['user_id']      = $item['user_id'] ? $item['user_id'] : $inUser->id;
        $article['title']        = cmsCore::request('title', 'str', '');
        
        $article['source_url']   = cmsCore::request('source_url', 'str', '');
        $article['source_name']  = cmsCore::request('source_name', 'str', '');
        if($article['source_url'] || $article['source_name']){ $article['showsource'] = 1; }
        
        $article['tags']         = cmsCore::request('tags', 'str', '');

        $article['description']  = cmsCore::request('description', 'html', '');
        $article['content']      = cmsCore::request('content', 'html', '');
        $article['description']  = cmsCore::badTagClear($article['description']);
        $article['content']      = cmsCore::badTagClear($article['content']);
        $article['description']  = $inDB->escape_string($article['description']);
        $article['content']      = $inDB->escape_string($article['content']);

        $article['published']    = $is_auto_add ? 1 : 0;
        if ($do=='editarticle'){
           $article['published'] = ($item['published'] == 0) ? $item['published'] : $article['published'];
        }
        $article['pubdate']      = $do=='editarticle' ? $item['pubdate'] : date('Y-m-d H:i');

        $article['meta_desc']    = $do=='addarticle' ? mb_strtolower($article['title']) : $inDB->escape_string($item['meta_desc']);
        $article['meta_keys']    = $do=='addarticle' ? $inCore->getKeywords($article['content']) : $inDB->escape_string($item['meta_keys']);

        $article['showdate']     = $do=='editarticle' ? $item['showdate'] : 1;
        $article['showlatest']   = $do=='editarticle' ? $item['showlatest'] : 1;
        $article['showpath']     = $do=='editarticle' ? $item['showpath'] : 1;
        $article['comments']     = $do=='editarticle' ? $item['comments'] : 1;

        $article['pagetitle']    = '';
        if ($do=='editarticle'){
           $article['tpl']       = $item['tpl'];
        }

        if (mb_strlen($article['title'])<2){ cmsCore::addSessionMessage($_LANG['REQ_TITLE'], 'error'); $errors = true; }
        if (mb_strlen($article['content'])<10){ cmsCore::addSessionMessage($_LANG['REQ_CONTENT'], 'error'); $errors = true; }

        if($errors) {

            // При добавлении статьи при ошибках сохраняем введенные поля
            if ($do=='addarticle'){
                    cmsUser::sessionPut('article', $article);
            }

            cmsCore::redirectBack();
        }

        //$article = cmsCore::callEvent('AFTER_EDIT_ARTICLE', $article);

        // добавление статьи
        if ($do=='addarticle'){
            $article_id = $model->addArticle($article);
        }

        // загрузка фото
        $file = 'news'.(@$article_id ? $article_id : $item['id']).'.jpg';

        if (cmsCore::request('delete_image', 'int', 0)){
            @unlink(PATH."/images/news/small/$file");
            @unlink(PATH."/images/news/medium/$file");
        }

        // Загружаем класс загрузки фото
        cmsCore::loadClass('upload_photo');
        $inUploadPhoto = cmsUploadPhoto::getInstance();
        // Выставляем конфигурационные параметры
        $inUploadPhoto->upload_dir    = PATH.'/images/news/';
        $inUploadPhoto->small_size_w  = $cfg['img_small_w'];
        $inUploadPhoto->medium_size_w = $cfg['img_big_w'];
        $inUploadPhoto->thumbsqr      = $cfg['img_sqr'];
        $inUploadPhoto->is_watermark  = $cfg['watermark'];
        $inUploadPhoto->input_name    = 'picture';
        $inUploadPhoto->filename      = $file;
        // Процесс загрузки фото
        $inUploadPhoto->uploadPhoto();

        // операции после добавления/редактирования статьи
        // добавление статьи
        if ($do=='addarticle'){

            // Получаем добавленную статью
            $article = $model->getArticle($article_id);

            if (!$article['published']){

                cmsCore::addSessionMessage($_LANG['ARTICLE_PREMODER_TEXT'], 'info');

                // отсылаем уведомление администраторам
                $link = '<a href="'.$model->getArticleURL($article['seolink']).'">'.$article['title'].'</a>';
                $message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_ARTICLE_SUBMIT']);
                $message = str_replace('%link%', $link, $message);

                cmsUser::sendMessageToGroup(USER_UPDATER, cmsUser::getAdminGroups(), $message);

            } else {

                if (IS_BILLING){
                    cmsBilling::process('news', 'add_news');
                }
                //cmsUser::checkAwards($inUser->id);
            }

            cmsCore::addSessionMessage($_LANG['ARTICLE_SAVE'], 'info');
            cmsCore::redirect('/news/my.html');

        }

        // Редактирование статьи
        if ($do=='editarticle'){

            $model->updateArticle($item['id'], $article, true);

            if (!$article['published']){

                    $link = '<a href="'.$model->getArticleURL($item['seolink']).'">'.$article['title'].'</a>';
                    $message = str_replace('%user%', cmsUser::getProfileLink($inUser->login, $inUser->nickname), $_LANG['MSG_ARTICLE_EDITED']);
                    $message = str_replace('%link%', $link, $message);

                    cmsUser::sendMessageToGroup(USER_UPDATER, cmsUser::getAdminGroups(), $message);

            }

            $mess = $article['published'] ? $_LANG['ARTICLE_SAVE'] : $_LANG['ARTICLE_SAVE'].' '.$_LANG['ARTICLE_PREMODER_TEXT'];
            cmsCore::addSessionMessage($mess, 'info');

            cmsCore::redirect($model->getArticleURL($item['seolink']));

        }

    }
}

/* ==================================================================================================== */
/* ======================== PUBLISH NEWS / ПУБЛИКАЦИЯ НОВОСТИ МОДЕРАТОРОМ ============================= */
/* ==================================================================================================== */
if ($do == 'publisharticle'){

    if (!$inUser->id){ cmsCore::error404(); }

    $article = $model->getArticle($id);
    if (!$article) { cmsCore::error404(); }

    // Редактор с правами на добавление без модерации или администраторы могут публиковать
    if (!(cmsUser::isUserCan('news/autoadd')) && !$inUser->is_admin) { cmsCore::error404(); }

    $inDB->setFlag('cms_news', $article['id'], 'published', 1);

    //cmsCore::callEvent('ADD_ARTICLE_DONE', $article);

    if (IS_BILLING){
        $author = $inDB->get_fields('cms_users', "id='{$article['user_id']}'", '*');
        //$action = cmsBilling::getAction('news', 'add_news');
        cmsBilling::process('news', 'add_news', '', $author);
    }

    $link = '<a href="'.$model->getArticleURL($article['seolink']).'">'.$article['title'].'</a>';
    $message = str_replace('%link%', $link, $_LANG['MSG_ARTICLE_ACCEPTED']);
    cmsUser::sendMessage(USER_UPDATER, $article['user_id'], $message);

    cmsCore::redirectBack();

}

/* ==================================================================================================== */
/* ======================== DELETE NEWS / УДАЛЕНИЕ НОВОСТИ ============================================ */
/* ==================================================================================================== */
if ($do=='deletearticle'){

    if (!$inUser->id){ cmsCore::error404(); }

    $article = $model->getArticle($id);
    if (!$article) { cmsCore::error404(); }

    // права доступа
    $is_author = cmsUser::isUserCan('news/delete') && ($article['user_id'] == $inUser->id);
    $is_editor = cmsUser::isUserCan('news/autoadd');

    if (!$is_author && !$is_editor && !$inUser->is_admin) { cmsCore::error404(); }

	if (!cmsCore::inRequest('goadd')){

            $inPage->setTitle($_LANG['ARTICLE_REMOVAL']);
            $inPage->addPathway($_LANG['ARTICLE_REMOVAL']);

            $confirm['title']              = $_LANG['ARTICLE_REMOVAL'];
            $confirm['text']               = $_LANG['ARTICLE_REMOVAL_TEXT'].' <a href="'.$model->getArticleURL($article['seolink']).'">'.$article['title'].'</a>?';
            $confirm['action']             = $_SERVER['REQUEST_URI'];
            $confirm['yes_button']         = array();
            $confirm['yes_button']['type'] = 'submit';
            $confirm['yes_button']['name'] = 'goadd';
            
            cmsPage::initTemplate('components', 'action_confirm.tpl')->
            assign('confirm', $confirm)->
            display('action_confirm.tpl');

	} else {

            $model->deleteArticle($article['id']);

            if ($_SERVER['HTTP_REFERER'] == '/news/my.html' ) {

                    cmsCore::addSessionMessage($_LANG['ARTICLE_DELETED'], 'info');
                    cmsCore::redirectBack();

            } else {

                // если удалили как администратор или редактор и мы не авторы статьи, отсылаем сообщение автору
                if (($is_editor || $inUser->is_admin) && $article['user_id'] != $inUser->id){

                        $link = '<a href="'.$model->getArticleURL($article['seolink']).'">'.$article['title'].'</a>';
                        $message = str_replace('%link%', $link, ($article['published'] ? $_LANG['MSG_ARTICLE_DELETED'] : $_LANG['MSG_ARTICLE_REJECTED']));
                        cmsUser::sendMessage(USER_UPDATER, $article['user_id'], $message);

                } else {
                        cmsCore::addSessionMessage($_LANG['ARTICLE_DELETED'], 'info');
                }

                cmsCore::redirect('/news');

            }

	}

}

/* ==================================================================================================== */
/* ======================== MY NEWS / МОИ НОВОСТИ ===================================================== */
/* ==================================================================================================== */
    if ($do=='my'){

        if (!cmsUser::isUserCan('news/add')){ cmsCore::error404(); }
        
        $inPage->setTitle($_LANG['MY_ARTICLES']);
        
        $inPage->addPathway($_LANG['CATALOG_ARTICLES'], '/news');
        $inPage->addPathway($_LANG['MY_ARTICLES']);

        $perpage = 15;

        // Условия
        $model->whereUserIs($inUser->id);

        // Общее количество статей
        $total = $model->getArticlesCount(false);

        // Сортировка и разбивка на страницы
        $inDB->orderBy('con.pubdate', 'DESC');
        $inDB->limitPage($page, $perpage);

        // Получаем статьи
        $content_list = $total ? 
        $model->getArticlesList(false) :
        array(); $inDB->resetConditions();
        
        cmsPage::initTemplate('components', 'com_news_my.tpl')->
        assign('articles', $content_list)->
        assign('total', $total)->
        assign('user_can_delete', cmsUser::isUserCan('news/delete'))->
        assign('pagebar', cmsPage::getPagebar($total, $page, $perpage, '/news/my%page%.html'))->
        display('com_news_my.tpl');

    }
} //function
?>