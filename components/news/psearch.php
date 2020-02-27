<?php

if(!defined('VALID_CMS')) { die('ACCESS DENIED'); }
	
function search_news($query, $look){

        $inDB   = cmsDatabase::getInstance();
        $searchModel = cms_model_search::initModel();

        global $_LANG;

        $sql = "SELECT con.*
                FROM cms_news con
                WHERE MATCH(con.title, con.content) AGAINST ('$query' IN BOOLEAN MODE) AND con.published = 1 LIMIT 100";

        $result = $inDB->query($sql);
	
        if ($inDB->num_rows($result)){

            cmsCore::loadLanguage('components/news');

            while($item = $inDB->fetch_assoc($result)){

                $result_array = array();

                $result_array['link']        = "/news/".$item['seolink'].".html";
                $result_array['place']       = 'Новости';
                $result_array['placelink']   = '/news';
                $result_array['description'] = $searchModel->getProposalWithSearchWord($item['content']);
                $result_array['title']       = $item['title'];
                $result_array['pubdate']     = $item['pubdate'];
                $result_array['session_id']  = session_id();

                $searchModel->addResult($result_array);			
            }
        }

        return;

} ?>