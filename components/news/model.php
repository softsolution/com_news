<?php

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }

class cms_model_news {

    public function __call($name, $arguments){
        exit("вызван несуществующий метод \"".$name."\"");
    }

    public function __construct(){
        $this->inDB   = cmsDatabase::getInstance();
        $this->config = cmsCore::getInstance()->loadComponentConfig('news');
        cmsCore::loadLanguage('components/news');
        cmsCore::loadLib('tags');
    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public static function getDefaultConfig() {

        $cfg = array (
                    'readdesc' => 0,
                    'is_url_cyrillic' => 0,
                    'perpage' => 20,
                    'orderby ' => 'pubdate',
                    'orderto' => 'DESC',
                    'show_comments' => 1,
                    'autokeys' => 0,
                    'img_small_w' => 100,
                    'img_big_w' => 200,
                    'img_sqr' => 1,
                    'watermark' => 1, 
                    'showrss' => 0, 
                    'maxcols' => 1,
                    'showdate' => 1,
                    'showcomm' => 1,
                    'showtags' => 1
                  );

        return $cfg;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function getCommentTarget($target, $target_id) {

        $result = array();

        switch($target){

            case 'news': $article = $this->inDB->get_fields('cms_news', "id='{$target_id}'", 'seolink, title');
                            if (!$article) { return false; }
                            $result['link']  = $this->getArticleURL($article['seolink']);
                            $result['title'] = $article['title'];
                            break;

        }

        return ($result ? $result : false);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */

    public function whereUserIs($user_id) {
        $this->inDB->where("con.user_id = '{$user_id}'");
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получаем статьи по заданным параметрам
     * @return array
     */
    public function getArticlesList($only_published=true) {

        $today = date("Y-m-d H:i:s");

        if ($only_published){
            $this->inDB->where("con.published = 1 AND con.pubdate <= '$today'");
        }

        $sql = "SELECT con.*,
                       con.pubdate as fpubdate,
                       u.nickname as author,
                       u.login as user_login
                FROM cms_news con
                LEFT JOIN cms_users u ON u.id = con.user_id
                WHERE 1=1 
                {$this->inDB->where}

                {$this->inDB->group_by}

                {$this->inDB->order_by}\n";

        if ($this->inDB->limit){
            $sql .= "LIMIT {$this->inDB->limit}";
        }

        $result = $this->inDB->query($sql);

        $this->inDB->resetConditions();

        if (!$this->inDB->num_rows($result)) { return false; }

        while($article = $this->inDB->fetch_assoc($result)){
            $article['fpubdate'] = cmsCore::dateFormat($article['fpubdate']);
            $article['tagline']  = cmsTagLine('news', $article['id'], true);
            $article['comments'] = cmsCore::getCommentsCount('news', $article['id']);
            $article['url']      = $this->getArticleURL($article['seolink']);
            $article['image']    = (file_exists(PATH.'/images/news/small/news'.$article['id'].'.jpg') ? 'news'.$article['id'].'.jpg' : '');
            $articles[] = $article;
        }

        return $articles;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает количество статей по заданным параметрам
     * @return int
     */
    public function getArticlesCount($only_published=true) {

        $today = date("Y-m-d H:i:s");

        if ($only_published){
            $this->inDB->where("con.published = 1 AND con.pubdate <= '$today'");
        }

        $sql = "SELECT 1 FROM cms_news con WHERE 1=1 {$this->inDB->where} {$this->inDB->group_by} ";

        $result = $this->inDB->query($sql);

        return $this->inDB->num_rows($result);

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Переносит просроченые статьи в архив
     * @return bool
     */
    public function moveArticlesToArchive() {

        return $this->inDB->query("UPDATE cms_news SET is_arhive = 1 WHERE is_end = 1 AND enddate < NOW()");

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Получает статью
     * @return array
     */
    public function getArticle($id_or_link) {

        if(is_numeric($id_or_link)){
            $where = "con.id = '$id_or_link'";
        } else {
            $where = "con.seolink = '$id_or_link'";
        }

        $sql = "SELECT con.*, u.nickname as author, u.login as user_login
                FROM cms_news con
                LEFT JOIN cms_users u ON u.id = con.user_id
                WHERE {$where} LIMIT 1";

        $result = $this->inDB->query($sql);

        if (!$this->inDB->num_rows($result)) { return false; }

        $article = $this->inDB->fetch_assoc($result);

        return $article;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * генерирует сеолинк для статьи
     * @param array $article Полный массив данных, включая id
     * @return str
     */
    public function getSeoLink($article){

        $seolink = cmsCore::strToURL(($article['url'] ? $article['url'] : $article['title']), $this->config['is_url_cyrillic']);

        if (!empty($article['id'])){
            $where = ' AND id<>'.$article['id'];
        } else {
            $where = '';
        }

        $is_exists = $this->inDB->get_field('cms_news', "seolink='{$seolink}'".$where, 'id');

        if ($is_exists) { $seolink .= '-'.(!empty($article['id']) ? $article['id'] : uniqid()); }

        return $seolink;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Возвращает урл статьи
     * @return str
     */
    public static function getArticleURL($seolink, $page=1){

        $page_section = ($page>1 ? '/page-'.$page : '');

        $url = '/news/'.$seolink.$page_section.'.html';

        return $url;

    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Удаляет статью
     * @return bool
     */
    public function deleteArticle($id){

        $this->inDB->delete('cms_news', "id='$id'", 1);
        $this->inDB->delete('cms_tags', "target='news' AND item_id='$id'");

        @unlink(PATH.'/images/news/small/news'.$id.'.jpg');
        @unlink(PATH.'/images/news/medium/news'.$id.'.jpg');

        cmsCore::deleteComments('news', $id);

        return true;

    }

    /**
     * Удаляет список статей
     * @param array $id_list
     * @return bool
     */
    public function deleteArticles($id_list){
        foreach($id_list as $id){
            $this->deleteArticle($id);
        }
        return true;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Добавляет новость
     * @param array $article
     * @return int
     */
    public function addArticle($article){

        //$article = cmsCore::callEvent('ADD_ARTICLE', $article);

        if ($article['url']) { $article['url'] = cmsCore::strToURL($article['url'], $this->config['is_url_cyrillic']); }

        $article['id'] = $this->inDB->insert('cms_news', $article);

        if ($article['id']){

            $article['seolink'] = $this->getSeoLink($article);
            $this->inDB->query("UPDATE cms_news SET seolink='{$article['seolink']}' WHERE id = '{$article['id']}'");

            cmsInsertTags($article['tags'], 'news', $article['id']);

            //оставлен event для пинга поисковых систем
            if ($article['published']) { cmsCore::callEvent('ADD_ARTICLE_DONE', $article); }

        }

        return $article['id'] ? $article['id'] : false;
    }

/* ==================================================================================================== */
/* ==================================================================================================== */
    /**
     * Обновляет новость
     * @return bool
     */
    public function updateArticle($id, $article, $not_upd_seo = false){

        $article['id']= $id;

        if(!$not_upd_seo){

            if (@$article['url']){
                    $article['url'] = cmsCore::strToURL($article['url'], $this->config['is_url_cyrillic']);
            }

            $article['seolink'] = $this->getSeoLink($article);

        } else { unset($article['seolink']); unset($article['url']); }

        if (!$article['user_id']) { $article['user_id'] = cmsUser::getInstance()->id; }

        //$article = cmsCore::callEvent('UPDATE_ARTICLE', $article);

        $this->inDB->update('cms_news', $article, $id);

        if(!$not_upd_seo){
            $this->updatenewsCommentsLink($id);
        }

        cmsInsertTags($article['tags'], 'news', $id);

        return true;

    }

    /**
     * Обновляет ссылки меню на статьи
     * @return bool
     */
    public function updatenewsCommentsLink($article_id){

        // Обновляем ссылки в комментариях
        $this->inDB->query("UPDATE cms_comments c, cms_news a SET c.target_link = CONCAT('/', a.seolink, '.html') WHERE a.id = '$article_id' AND c.target = 'news' AND c.target_id = a.id");
        return true;

    }




}