<?php
/* ****************************************************************************************** */
/* created by soft-solution.ru                                                                */
/* backend.php of component news for InstantCMS 1.10.3                                        */
/* ****************************************************************************************** */

if(!defined('VALID_CMS_ADMIN')) { die('ACCESS DENIED'); }

    $inCore = cmsCore::getInstance();
    $inUser = cmsUser::getInstance();
    $inDB   = cmsDatabase::getInstance();

    global $_LANG;
    
    $inCore->loadLib('tags');
    
    cmsCore::loadModel('news');
    $model = new cms_model_news();
    
    $cfg = $inCore->loadComponentConfig('news');

    $opt = $inCore->request('opt', 'str', 'list_items');
    $component_id  = $inCore->request('id', 'int', 0);
    
    //echo '<h3>Новости</h3>';

    cpAddPathway('Новости', '?view=components&do=config&id='.$component_id);
    //$GLOBALS['cp_page_head'][] = '<script language="JavaScript" type="text/javascript" src="/admin/components/news/js/common.js"></script>';
    
    $toolmenu = array();
    
    if($opt =='add_item' || $opt == 'config'){

        $toolmenu[0]['icon'] = 'save.gif';
        $toolmenu[0]['title'] = 'Сохранить';
        $toolmenu[0]['link'] = 'javascript:document.addform.submit();';

        $toolmenu[1]['icon'] = 'cancel.gif';
        $toolmenu[1]['title'] = 'Отмена';
        $toolmenu[1]['link'] = 'javascript:history.go(-1);';

    } else {
        
        $toolmenu[1]['icon'] = 'liststuff.gif';
        $toolmenu[1]['title'] = 'Все новости';
        $toolmenu[1]['link'] = '?view=components&do=config&id='.$component_id;

        $toolmenu[2]['icon'] = 'page_error.png';
        $toolmenu[2]['title'] = 'Новости требующие модерации';
        $toolmenu[2]['link'] = '?view=components&do=config&id='.$component_id.'&opt=list_items&notpublic=1';

        $toolmenu[3]['icon'] = 'newstuff.gif';
        $toolmenu[3]['title'] = 'Добавить новость';
        $toolmenu[3]['link'] = '?view=components&do=config&id='.$component_id.'&opt=add_item';

        if($opt == 'list_items' || $opt == 'show_item' || $opt == 'hide_item'){
            $toolmenu[11]['icon'] = 'edit.gif';
            $toolmenu[11]['title'] = 'Редактировать выбранные';
            $toolmenu[11]['link'] = "javascript:checkSel('?view=components&do=config&id=".$component_id."&opt=edit_item&multiple=1');";

            $toolmenu[12]['icon'] = 'show.gif';
            $toolmenu[12]['title'] = 'Публиковать выбранные';
            $toolmenu[12]['link'] = "javascript:checkSel('?view=components&do=config&id=".$component_id."&opt=show_item&multiple=1');";

            $toolmenu[13]['icon'] = 'hide.gif';
            $toolmenu[13]['title'] = 'Скрыть выбранные';
            $toolmenu[13]['link'] = "javascript:checkSel('?view=components&do=config&id=".$component_id."&opt=hide_item&multiple=1');";

            $toolmenu[14]['icon'] = 'delete.gif';
            $toolmenu[14]['title'] = 'Удалить выбранные';
            $toolmenu[14]['link'] = "javascript:checkSel('?view=components&do=config&id=".$component_id."&opt=delete_item&multiple=1');";
        }

        $toolmenu[15]['icon'] = 'config.gif';
        $toolmenu[15]['title'] = 'Настройки';
        $toolmenu[15]['link'] = '?view=components&do=config&id='.$component_id.'&opt=config';
    
    }
    
    cpToolMenu($toolmenu);

/* ==================================================================================================== */
/* =================================== СОХРАНЯЕМ НАСТРОЙКИ ============================================ */
/* ==================================================================================================== */
    
    if($opt=='saveconfig'){
        
        if(!cmsCore::validateForm()) { cmsCore::error404(); }
        
        //настройки
        $cfg['keywords']          = cmsCore::request('keywords', 'html');
        $cfg['metadesc']          = cmsCore::request('metadesc', 'html');
        $cfg['is_url_cyrillic']   = cmsCore::request('is_url_cyrillic', 'int', 0);
        $cfg['readdesc']          = cmsCore::request('readdesc', 'int', 0);
        $cfg['autokeys']          = cmsCore::request('autokeys', 'int', 0);
        
        $cfg['perpage']           = cmsCore::request('perpage', 'int', 20);
        $cfg['orderby']           = cmsCore::request('orderby', 'str', 'pubdate');
        $cfg['orderto']           = cmsCore::request('orderto', 'str', 'ASC');

        $cfg['show_comments']     = cmsCore::request('show_comments', 'int');
        
        $cfg['img_small_w']       = cmsCore::request('img_small_w', 'int', 100);
        $cfg['img_big_w']         = cmsCore::request('img_big_w', 'int', 200);
        $cfg['img_sqr']           = cmsCore::request('img_sqr', 'int');
        $cfg['watermark']         = cmsCore::request('watermark', 'int');
        
        $cfg['showdate'] 	  = cmsCore::request('showdate', 'int');
        $cfg['showcomm'] 	  = cmsCore::request('showcomm', 'int');
        $cfg['showtags'] 	  = cmsCore::request('showtags', 'int');
          
        $cfg['showrss']           = cmsCore::request('showrss', 'int');
        $cfg['maxcols'] 	  = cmsCore::request('maxcols', 'int', 1);

        $inCore->saveComponentConfig('news', $cfg);
        cmsCore::addSessionMessage('Настройки сохранены', 'info');

        $inCore->redirect('index.php?view=components&do=config&id='.$component_id.'&opt=list_items');
    }
    
/* ==================================================================================================== */
/* =================================== НАСТРОЙКИ КОМПОНЕНТА =========================================== */
/* ==================================================================================================== */
    
    if ($opt=='config') {
	
        cpAddPathway('Новости', '?view=components&do=config&id='.$component_id);
        cpAddPathway('Настройки', '?view=components&do=config&id='.$component_id.'&opt=config');
		
        echo '<h3>Настройки</h3>';
        
    ?>
		
    <form action="index.php?view=components&do=config&id=<?php echo $component_id;?>&opt=config" method="post" target="_self" id="addform" name="addform">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />
        <div id="config_tabs" style="margin-top:12px;" class="uitabs">
			
            <ul id="tabs">
                <li><a href="#basic"><span>Общие</span></a></li>
                <li><a href="#news"><span>Новости</span></a></li>
            </ul>
			
            <div id="basic">
                <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
                    <tr>
                        <td valign="top">
                            <strong>Ключевые слова для компонента:</strong><br />
                            <span class="hinttext">Через запятую, 10-15 слов.</span>
                        </td>
                        <td>
                            <textarea name="keywords" style="width:350px" rows="3" id="keywords"><?php echo @$cfg['keywords'];?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <strong>Описание для компонента:</strong><br />
                            <span class="hinttext">Не более 250 символов.</span>
                        </td>
                        <td>
                            <textarea name="metadesc" style="width:350px" rows="3" id="metadesc"><?php echo @$cfg['metadesc'];?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Генерировать кириллические url новостей: </strong></td>
                        <td width="110">
                            <label><input name="is_url_cyrillic" type="radio" value="1" <?php if ($cfg['is_url_cyrillic']) { echo 'checked="checked"'; } ?>/> Да </label>
                            <label><input name="is_url_cyrillic" type="radio" value="0" <?php if (!$cfg['is_url_cyrillic']) { echo 'checked="checked"'; } ?>/> Нет </label>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Выводить анонсы при просмотре статей: </strong></td>
                        <td width="110">
                            <input name="readdesc" type="radio" value="1" <?php if ($cfg['readdesc']) { echo 'checked="checked"'; } ?>/> Да
                            <input name="readdesc" type="radio" value="0" <?php if (!$cfg['readdesc']) { echo 'checked="checked"'; } ?>/> Нет
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Автоматически генерировать<br />ключевые слова и описания для статей:</strong>
                        </td>
                        <td valign="top">
                            <input name="autokeys" type="radio" value="1" <?php if ($cfg['autokeys']) { echo 'checked="checked"'; } ?>/> Да
                            <input name="autokeys" type="radio" value="0" <?php if (!$cfg['autokeys']) { echo 'checked="checked"'; } ?>/> Нет
                        </td>
                    </tr>
                </table>
            </div>

            <div id="news">
                <table width="680" border="0" cellpadding="10" cellspacing="0" class="proptable">
                    <tr>
                        <td valign="top"><strong>Новостей на странице:</strong></td>
                        <td width="130" valign="top">
                            <input name="perpage" type="text" id="perpage" value="<?php echo @$cfg['perpage'];?>" size="10" /> шт.
                        </td>
                    </tr>                  
                    <tr>
                        <td><strong>Сортировка:</strong></td>
                        <td>
                            <select name="orderby" style="width:140px">
                                <option value="title" <?php if (@$cfg['orderby']=='title') { echo 'selected="selected"'; } ?>>По алфавиту</option>
                                <option value="id" <?php if (@$cfg['orderby']=='id') { echo 'selected="selected"'; } ?>>по id</option>
                                <option value="pubdate" <?php if (@$cfg['orderby']=='pubdate') { echo 'selected="selected"'; } ?>>по дате</option>
                            </select>
                            
                            <select name="orderto" style="width:140px">
                                <option value="ASC" <?php if($cfg['orderto']=='ASC'){ ?>selected="selected"<?php } ?>>по возрастанию</option>
                                <option value="DESC" <?php if($cfg['orderto']=='DESC'){ ?>selected="selected"<?php } ?>>по убыванию</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Ширина изображения в анонсе:</strong><br />
                        </td>
                        <td><input name="img_small_w" type="text" id="img_small_w" size="5" value="<?php echo @$cfg['img_small_w'];?>"/> px</td>
                    </tr>
                    <tr>
                        <td><strong>Ширина большого изображения:</strong><br />
                        <td><input name="img_big_w" type="text" id="img_big_w" size="5" value="<?php echo @$cfg['img_big_w'];?>"/> px</td>
                    </tr>
                    <tr>
                        <td><strong>Квадратные изображения:</strong></td>
                        <td>
                            <select name="img_sqr" id="select" style="width:60px">
                                <option value="1" <?php if (@$cfg['img_sqr']=='1') { echo 'selected="selected"'; } ?>>Да</option>
                                <option value="0" <?php if (@$cfg['img_sqr']=='0') { echo 'selected="selected"'; } ?>>Нет</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Наносить водяной знак:</strong></td>
                        <td>
                            <label><input name="watermark" type="radio" value="1" <?php if ($cfg['watermark']) { echo 'checked="checked"'; } ?> /> Да</label>
                            <label><input name="watermark" type="radio" value="0"  <?php if (!$cfg['watermark']) { echo 'checked="checked"'; } ?> /> Нет</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <strong>Комментарии для новостей:</strong>
                        </td>
                        <td valign="top">
                            <label><input type="radio" value="1" name="show_comments" <?php if (@$cfg['show_comments']) { echo 'checked="checked"'; } ?> /> Да</label>
                            <label><input type="radio" value="0" name="show_comments" <?php if (@!$cfg['show_comments']) { echo 'checked="checked"'; } ?> /> Нет</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <strong>Показывать теги новостей:</strong>
                        </td>
                        <td valign="top">
                            <label><input type="radio" value="1" name="showtags" <?php if (@$cfg['showtags']) { echo 'checked="checked"'; } ?> /> Да</label>
                            <label><input type="radio" value="0" name="showtags" <?php if (@!$cfg['showtags']) { echo 'checked="checked"'; } ?> /> Нет</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <strong>Показывать даты новостей:</strong>
                        </td>
                        <td valign="top">
                            <label><input type="radio" value="1" name="showdate" <?php if (@$cfg['showdate']) { echo 'checked="checked"'; } ?> /> Да</label>
                            <label><input type="radio" value="0" name="showdate" <?php if (@!$cfg['showdate']) { echo 'checked="checked"'; } ?> /> Нет</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <strong>Показывать число комментариев:</strong>
                        </td>
                        <td valign="top">
                            <label><input type="radio" value="1" name="showcomm" <?php if (@$cfg['showcomm']) { echo 'checked="checked"'; } ?> /> Да</label>
                            <label><input type="radio" value="0" name="showcomm" <?php if (@!$cfg['showcomm']) { echo 'checked="checked"'; } ?> /> Нет</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <strong>Показывать иконку RSS:</strong>
                        </td>
                        <td valign="top">
                            <label><input type="radio" value="1" name="showrss" <?php if (@$cfg['showrss']) { echo 'checked="checked"'; } ?> /> Да</label>
                            <label><input type="radio" value="0" name="showrss" <?php if (@!$cfg['showrss']) { echo 'checked="checked"'; } ?> /> Нет</label>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Число колонок для вывода новостей</strong></td>
                        <td>
                            <?php if (!isset($cfg['maxcols'])) { $cfg['maxcols'] = 1; } ?>
                            <input name="maxcols" type="text" id="maxcols" size="5" value="<?php echo @$cfg['maxcols'];?>" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <p>
            <input name="opt" type="hidden" value="saveconfig" />
            <input name="save" type="submit" id="save" value="Сохранить" />
            <input name="back" type="button" id="back" value="Отмена" onclick="window.location.href='?view=components&do=config&id=<?php echo $component_id; ?>&opt=list_items';"/>
        </p>
        
    </form>	
	
    <?php } 
    
/* ========================================================================== */
/* =============================== Публикация/скрытие/удаление записи ======= */
/* ========================================================================== */

    if ($opt == 'show_item'){
        if (!isset($_REQUEST['item'])){
            if ($inCore->inRequest('item_id')) {
                dbShow('cms_news', $inCore->request('item_id', 'int'));
            }
            echo '1';
            exit;
        } else {
            dbShowList('cms_news', $inCore->request('item', 'array_int'));
            $inCore->redirectBack();
        }
    }

    if ($opt == 'hide_item'){
        if (!isset($_REQUEST['item'])){
            if ($inCore->inRequest('item_id')) {
                dbHide('cms_news', $inCore->request('item_id', 'int'));
            }
            echo '1';
            exit;
        } else {
            dbHideList('cms_news', $inCore->request('item', 'array_int'));
            $inCore->redirectBack();
        }
    }

    if ($opt == 'delete_item'){
        
        if ($inCore->inRequest('item_id')){
            $item_id = cmsCore::request('item_id', 'int');
            $model->deleteArticle($item_id);
            cmsCore::addSessionMessage('Новость успешно удалена', 'success');
        }

        if ($inCore->inRequest('item')){
            $items = cmsCore::request('item', 'array');
            $model->deleteArticles($items);
            cmsCore::addSessionMessage('Новости успешно удалены', 'success');
        }
        
        $inCore->redirectBack();
    }

/* ========================================================================== */
/* =============================== Редактируем запись ======================= */
/* ========================================================================== */
        
    if ($opt == 'update_item'){
        if (!cmsCore::validateForm()) { cmsCore::error404(); }
        
        if($inCore->inRequest('item_id')) {

            $id                     = cmsCore::request('item_id', 'int', 0);

            $article['title']       = cmsCore::request('title', 'str');
            $article['url']         = cmsCore::request('url', 'str');
            
            $article['showsource']  = cmsCore::request('showsource', 'int', 0);
            $article['source_url']  = $inDB->escape_string($inCore->request('source_url', 'str'));
            $article['source_name'] = $inDB->escape_string($inCore->request('source_name', 'str'));
            
            $article['description'] = cmsCore::request('description', 'html', '');
            $article['description'] = $inDB->escape_string($article['description']);
            $article['content']     = cmsCore::request('content', 'html', '');
            $article['content']     = $inDB->escape_string($article['content']);
            $article['published']   = cmsCore::request('published', 'int', 0);
            
            $article['showdate']    = cmsCore::request('showdate', 'int', 0);
            $article['showlatest']  = cmsCore::request('showlatest', 'int', 0);
            $article['showpath']    = cmsCore::request('showpath', 'int', 0);
            $article['comments']    = cmsCore::request('comments', 'int', 0);

            $article['pagetitle']   = cmsCore::request('pagetitle', 'str', '');
            $article['tags']        = cmsCore::request('tags', 'str');
            
            $olddate                = cmsCore::request('olddate', 'str');
            $pubdate                = cmsCore::request('pubdate', 'str', $olddate);

            $article['user_id']     = cmsCore::request('user_id', 'int', $inUser->id);
            
            $article['tpl']         = cmsCore::request('tpl', 'str', 'com_news_read.tpl');

            $date = explode('.', $pubdate);
            $article['pubdate'] = $date[2] . '-' . $date[1] . '-' . $date[0] . ' ' .date('H:i');

            $autokeys               = cmsCore::request('autokeys', 'int');

            switch($autokeys){
                case 1: $article['meta_keys'] = $inCore->getKeywords($article['content']);
                        $article['meta_desc'] = $article['title'];
                        break;

                case 2: $article['meta_desc'] = strip_tags($article['description']);
                        $article['meta_keys'] = $article['tags'];
                        break;

                case 3: $article['meta_desc'] = cmsCore::request('meta_desc', 'str');
                        $article['meta_keys'] = cmsCore::request('meta_keys', 'str');
                        break;
            }

            $model->updateArticle($id, $article);
            
            $file = 'news'.$id.'.jpg';

            if ($inCore->request('delete_image', 'int', 0)){
                @unlink(PATH."/images/news/small/$file");
                @unlink(PATH."/images/news/medium/$file");
            } else {

                // Загружаем класс загрузки фото
                cmsCore::loadClass('upload_photo');
                $inUploadPhoto = cmsUploadPhoto::getInstance();
                // Выставляем конфигурационные параметры
                $inUploadPhoto->upload_dir    = PATH.'/images/news/';
                $inUploadPhoto->small_size_w  = $model->config['img_small_w'];
                $inUploadPhoto->medium_size_w = $model->config['img_big_w'];
                $inUploadPhoto->thumbsqr      = $model->config['img_sqr'];
                $inUploadPhoto->is_watermark  = $model->config['watermark'];
                $inUploadPhoto->input_name    = 'picture';
                $inUploadPhoto->filename      = $file;
                // Процесс загрузки фото
                $inUploadPhoto->uploadPhoto();

            }
            
            cmsCore::addSessionMessage('Новость успешно сохранена', 'success');
            if (!isset($_SESSION['editlist']) || @sizeof($_SESSION['editlist'])==0){
                $inCore->redirect('index.php?view=components&do=config&id='.$component_id.'&opt=list_items');
            } else {
                $inCore->redirect('index.php?view=components&do=config&id='.$component_id.'&opt=edit_item');
            }
        }
    }
        
/* ========================================================================== */
/* =============================== Добавляем новость ======================== */
/* ========================================================================== */
        
    if ($opt == 'submit_item'){
        if (!cmsCore::validateForm()) { cmsCore::error404(); }
        
        $article['title']       = cmsCore::request('title', 'str');
        $article['url']         = cmsCore::request('url', 'str');
        
        $article['showsource']  = cmsCore::request('showsource', 'int', 0);
        $article['source_url']  = $inDB->escape_string($inCore->request('source_url', 'str'));
        $article['source_name'] = $inDB->escape_string($inCore->request('source_name', 'str'));

        $article['description'] = cmsCore::request('description', 'html', '');
        $article['description'] = $inDB->escape_string($article['description']);
        $article['content']     = cmsCore::request('content', 'html', '');
        $article['content']    	= $inDB->escape_string($article['content']);
        
        $article['published']   = cmsCore::request('published', 'int', 0);

        $article['showdate']    = cmsCore::request('showdate', 'int', 0);
        $article['showlatest']  = cmsCore::request('showlatest', 'int', 0);
        $article['showpath']    = cmsCore::request('showpath', 'int', 0);
        $article['comments']    = cmsCore::request('comments', 'int', 0);
        
        $article['pagetitle']   = cmsCore::request('pagetitle', 'str', '');

        $article['tags']        = cmsCore::request('tags', 'str');
        
        $article['pubdate']     = cmsCore::request('pubdate', 'str');
        
        $date                   = explode('.', $article['pubdate']);
        $article['pubdate']     = $date[2] . '-' . $date[1] . '-' . $date[0] . ' ' .date('H:i');

        $article['user_id']     = cmsCore::request('user_id', 'int', $inUser->id);

        $article['tpl']         = cmsCore::request('tpl', 'str', 'com_news_read.tpl');

        $autokeys               = cmsCore::request('autokeys', 'int');

        switch($autokeys){
            case 1: $article['meta_keys'] = $inCore->getKeywords($article['content']);
                    $article['meta_desc'] = $article['title'];
                    break;

            case 2: $article['meta_desc'] = strip_tags($article['description']);
                    $article['meta_keys'] = $article['tags'];
                    break;

            case 3: $article['meta_desc'] = cmsCore::request('meta_desc', 'str');
                    $article['meta_keys'] = cmsCore::request('meta_keys', 'str');
                    break;
        }
        
        $article['id']          = $model->addArticle($article);

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
        $inUploadPhoto->filename      = 'news'.$article['id'].'.jpg';
        // Процесс загрузки фото
        $inUploadPhoto->uploadPhoto();

        cmsCore::addSessionMessage('Новость успешно добавлена', 'success');
        cmsUser::clearCsrfToken();
        $inCore->redirect('index.php?view=components&do=config&id='.$component_id.'&opt=list_items');

    }
        
/* ========================================================================== */
/* =============================== Форма добавления/редактирования записи === */
/* ========================================================================== */

   if ($opt == 'add_item' || $opt == 'edit_item'){

        require('../includes/jwtabs.php');
        $GLOBALS['cp_page_head'][] = jwHeader();

        if ($opt=='add_item'){
            echo '<h3>Добавить новость</h3>';
            cpAddPathway('Добавить новость', 'index.php?view=components&do=config&id='.$component_id.'&opt=add_item');
            $mod['tpl'] = 'com_news_read.tpl';
        } else {
            
            if (isset($_REQUEST['item'])){
                $_SESSION['editlist'] = $_REQUEST['item'];
            }

            $ostatok = '';

            if (isset($_SESSION['editlist'])){
                   $id = array_shift($_SESSION['editlist']);
                   if (sizeof($_SESSION['editlist'])==0) { unset($_SESSION['editlist']); } else
                   { $ostatok = '(На очереди: '.sizeof($_SESSION['editlist']).')'; }
            } else { $id = (int)$_REQUEST['item_id']; }

            $sql = "SELECT *, DATE_FORMAT(pubdate, '%d.%m.%Y') as pubdate
                    FROM cms_news
                    WHERE id = $id LIMIT 1";
            
            $result = $inDB->query($sql);
            if ($inDB->num_rows($result)){
                $mod = $inDB->fetch_assoc($result);
            }

            echo '<h3>Редактировать новость '.$ostatok.'</h3>';
            cpAddPathway($mod['title'], 'index.php?view=components&do=config&id='.$component_id.'&opt=edit_note&item_id='.$mod['id']);
        }
        
	?>
    <form id="addform" name="addform" method="post" action="index.php?view=components&amp;do=config&amp;id=<?php echo $component_id; ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo cmsUser::getCsrfToken(); ?>" />

        <table class="proptable" width="100%" cellpadding="15" cellspacing="2">
            <tr>
                <!-- главная ячейка -->
                <td valign="top">
                    <table width="100%" cellpadding="0" cellspacing="4" border="0">
                        <tr>
                            <td valign="top">
                                <div><strong>Заголовок новости</strong></div>
                                <div>
                                    <input name="title" type="text" id="title" style="width:100%" value="<?php echo htmlspecialchars($mod['title']);?>" /></td>
                                </div>
                            </td>
                            <td width="130" valign="top">
                                <div><strong>Дата публикации</strong></div>
                                <div>
                                    <input name="pubdate" type="text" id="pubdate" style="width:100px" <?php if(@!$mod['pubdate']) { echo 'value="'.date('d.m.Y').'"'; } else { echo 'value="'.$mod['pubdate'].'"'; } ?>/>
                                    <input type="hidden" name="olddate" value="<?php echo @$mod['pubdate']?>" />
                                </div>
                            </td>
                            <td width="16" valign="bottom" style="padding-bottom:10px">
                                <input type="checkbox" name="showdate" id="showdate" title="Показывать дату и автора" value="1" <?php if ($mod['showdate'] || $opt=='add_item') { echo 'checked="checked"'; } ?>/>
                            </td>
                            <td width="160" valign="top">
                                <div><strong>Шаблон новости</strong></div>
                                <div><input name="tpl" type="text" style="width:160px" value="<?php echo @$mod['tpl'];?>"></div>
                            </td>

                        </tr>
                    </table>
                    
                    <div><strong>Анонс новости (не обязательно)</strong></div>
                    <div><?php $inCore->insertEditor('description', $mod['description'], '200', '100%'); ?></div>

                    <div><strong>Полный текст новости</strong></div>
                    <?php insertPanel(); ?>
                    <div><?php $inCore->insertEditor('content', $mod['content'], '400', '100%'); ?></div>

                    <div><strong>Теги новости</strong></div>
                    <div><input name="tags" type="text" id="tags" style="width:99%" value="<?php if (isset($mod['id'])) { echo cmsTagLine('news', $mod['id'], false); } ?>" /></div>

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20">
                                <input type="radio" name="autokeys" id="autokeys1" <?php if ($opt=='add_item' && $cfg['autokeys']){ ?>checked="checked"<?php } ?> value="1"/>
                            </td>
                            <td>
                                <label for="autokeys1"><strong>Автоматически сгенерировать ключевые слова и описание</strong></label>
                            </td>
                        </tr>
                        <tr>
                            <td width="20">
                                <input type="radio" name="autokeys" id="autokeys2" value="2"/>
                            </td>
                            <td>
                                <label for="autokeys2"><strong>Использовать теги и анонс как ключевые слова и описание</strong></label>
                            </td>
                        </tr>
                        <tr>
                            <td width="20">
                                <input type="radio" name="autokeys" id="autokeys3" value="3" <?php if ($opt=='edit_item' || !$cfg['autokeys']){ ?>checked="checked"<?php } ?>/>
                            </td>
                            <td>
                                <label for="autokeys3"><strong>Заполнить ключевые слова и описание вручную</strong></label>
                            </td>
                        </tr>
                    </table>

                </td>

                <!-- боковая ячейка -->
                <td width="300" valign="top" style="background:#ECECEC;">

                    <?php ob_start(); ?>

                    {tab=Публикация}

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20"><input type="checkbox" name="published" id="published" value="1" <?php if ($mod['published'] || $opt=='add_item') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="published"><strong>Публиковать новость</strong></label></td>
                        </tr>
                    </table>

                    <div style="margin-bottom:10px">
                        <select name="showpath" id="showpath" style="width:99%">
                            <option value="0" <?php if (@!$mod['showpath']) { echo 'selected="selected"'; } ?>>Глубиномер: Только название</option>
                            <option value="1" <?php if (@$mod['showpath']) { echo 'selected="selected"'; } ?>>Глубиномер: Полный путь</option>
                        </select>
                    </div>

                    <div style="margin-top:15px">
                        <strong>URL страницы</strong><br/>
                        <div style="color:gray">Если не указан, генерируется из заголовка</div>
                    </div>
                    <div>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td><input type="text" name="url" value="<?php echo $mod['url']; ?>" style="width:100%"/></td>
                                <td width="40" align="center">.html</td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-top:10px">
                        <strong>Автор новости</strong>
                    </div>
                    <div>
                        <select name="user_id" id="user_id" style="width:99%">
                          <?php
                              if (isset($mod['user_id'])) {
                                    echo $inCore->getListItems('cms_users', $mod['user_id'], 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                              } else {
                                    echo $inCore->getListItems('cms_users', $inUser->id, 'nickname', 'ASC', 'is_deleted=0 AND is_locked=0', 'id', 'nickname');
                              }
                          ?>
                        </select>
                    </div>

                    <div style="margin-top:12px"><strong>Фотография</strong></div>
                    <div style="margin-bottom:10px">
                        <?php
                            if ($opt=='edit_item'){
                                if (file_exists(PATH.'/images/news/small/news'.$mod['id'].'.jpg')){
                        ?>
                        <div style="margin-top:3px;margin-bottom:3px;padding:10px;border:solid 1px gray;text-align:center">
                            <img src="/images/news/small/news<?php echo $id; ?>.jpg" border="0" />
                        </div>
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="16"><input type="checkbox" id="delete_image" name="delete_image" value="1" /></td>
                                <td><label for="delete_image">Удалить фотографию</label></td>
                            </tr>
                        </table>
                        <?php
                                }
                            }
                        ?>
                        <input type="file" name="picture" style="width:100%" />
                    </div>

                    <div style="margin-top:25px"><strong>Параметры публикации</strong></div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20"><input type="checkbox" name="showlatest" id="showlatest" value="1" <?php if ($mod['showlatest'] || $opt=='add_item') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="showlatest">Показывать в модуле "Последние новости"</label></td>
                        </tr>
                        <tr>
                            <td width="20"><input type="checkbox" name="comments" id="comments" value="1" <?php if ($mod['comments'] || $opt=='add_item') { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="comments">Разрешить комментарии</label></td>
                        </tr>
                    </table>
                    
                    {tab=SEO}

                    <div style="margin-top:5px">
                        <strong>Заголовок страницы</strong><br/>
                        <span class="hinttext">Если не указан, будет совпадать с названием</span>
                    </div>
                    <div>
                        <input name="pagetitle" type="text" id="pagetitle" style="width:99%" value="<?php if (isset($mod['pagetitle'])) { echo htmlspecialchars($mod['pagetitle']); } ?>" />
                    </div>

                    <div style="margin-top:20px">
                        <strong>Ключевые слова</strong><br/>
                        <span class="hinttext">Через запятую, 10-15 слов</span>
                    </div>
                    <div>
                         <textarea name="meta_keys" style="width:97%" rows="2" id="meta_keys"><?php echo htmlspecialchars($mod['meta_keys']);?></textarea>
                    </div>

                    <div style="margin-top:20px">
                        <strong>Описание</strong><br/>
                        <span class="hinttext">Не более 250 символов</span>
                    </div>
                    <div>
                         <textarea name="meta_desc" style="width:97%" rows="4" id="meta_desc"><?php echo htmlspecialchars($mod['meta_desc']);?></textarea>
                    </div>
                    
                    {tab=Источник}
                    
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="checklist">
                        <tr>
                            <td width="20"><input type="checkbox" name="showsource" id="showsource" value="1" <?php if ($mod['showsource']) { echo 'checked="checked"'; } ?>/></td>
                            <td><label for="showsource">Показывать источник</label></td>
                        </tr>
                    </table>
                    
                    <div style="margin-top:5px">
                        <strong>Источник: URL (с http://)</strong>
                    </div>
                    <div>
                        <input name="source_url" type="text" id="source_url" style="width:99%" value="<?php if (isset($mod['source_url'])) { echo htmlspecialchars($mod['source_url']); } ?>" />
                    </div>
                    
                    <div style="margin-top:5px">
                        <strong>Источник: Название</strong><br/>
                        <span class="hinttext">Если не указан, используется URL</span>
                    </div>
                    <div>
                        <input name="source_name" type="text" id="source_name" style="width:99%" value="<?php if (isset($mod['source_name'])) { echo htmlspecialchars($mod['source_name']); } ?>" />
                    </div>

                    {/tabs}

                    <?php echo jwTabs(ob_get_clean()); ?>

                </td>

            </tr>
        </table>
        <p>
            <input name="add_mod" type="submit" id="add_mod" <?php if ($opt=='add_item') { echo 'value="Добавить новость"'; } else { echo 'value="Сохранить новость"'; } ?> />
            <input name="back" type="button" id="back" value="Отмена" onclick="window.history.back();"/>
            <input name="opt" type="hidden" id="opt" <?php if ($opt=='add_item') { echo 'value="submit_item"'; } else { echo 'value="update_item"'; } ?> />
            <?php
                if ($opt=='edit_item'){
                    echo '<input name="item_id" type="hidden" value="'.$mod['id'].'" />';
                }
            ?>
        </p>
    </form>
    <?php }
    
/* ========================================================================== */
/* =============================== Все новости ============================== */
/* ========================================================================== */
    
    if ($opt == 'list_items'){
        
        cpAddPathway('Новости', '?view=components&do=config&id='.$component_id.'&opt=list_items');
        echo '<h3>Новости</h3>';

        $fields[] = array('title'=>'id', 'field'=>'id', 'width'=>'30');
        $fields[] = array('title'=>'Дата', 'field'=>'pubdate', 'width'=>'80', 'filter'=>'15', 'fdate'=>'%Y-%m-%d');
        $fields[] = array('title'=>'Заголовок', 'field'=>'title', 'width'=>'', 'filter'=>'30', 'link'=>'?view=components&do=config&id='.$component_id.'&opt=edit_item&item_id=%id%');
        $fields[] = array('title'=>'Показ', 'field'=>'published', 'width'=>'50', 'do'=>'opt', 'do_suffix'=>'_item');
        
        $actions[] = array('title'=>'Редактировать', 'icon'=>'edit.gif', 'link'=>'?view=components&do=config&id='.$component_id.'&opt=edit_item&item_id=%id%');
        $actions[] = array('title'=>'Удалить', 'icon'=>'delete.gif', 'confirm'=>'Удалить запись?', 'link'=>'?view=components&do=config&id='.$component_id.'&opt=delete_item&item_id=%id%');

        $notpublic   = $inCore->request('notpublic', 'int', 0);
        if($notpublic){
            $where = 'published = 0';
        } else {
            $where = '';
        }

        cpListTable('cms_news', $fields, $actions, $where, 'pubdate DESC');

    }
?>