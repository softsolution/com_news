<?php

// ========================================================================== //

    function info_module_mod_news(){

        // Описание модуля

        //Заголовок (на сайте)
        
        $_module['title']        = 'Последние новости';

        //Название (в админке)
        $_module['name']         = 'Последние новости';

        //описание
        $_module['description']  = 'Модуль Последние новости компонента Новости';
        
        //ссылка (идентификатор)
        $_module['link']         = 'mod_news';
        
        //позиция
        $_module['position']     = 'sidebar';

        //автор
        $_module['author']       = '<a href="http://soft-solution.ru" target="_blank">soft-solution.ru</a>';

        //текущая версия
        $_module['version']      = '1.0';


        // Настройки по-умолчанию
        $_module['config'] = array();

        $_module['config']['newscount'] = 5;
        $_module['config']['showdesc'] = 1;
        $_module['config']['showdate'] = 1;
        $_module['config']['showcom'] = 1;
        $_module['config']['showrss'] = 0;
        $_module['config']['is_pag'] = 0;
        return $_module;

    }

// ========================================================================== //

    function install_module_mod_news(){

        return true;

    }

// ========================================================================== //

    function upgrade_module_mod_news(){

        return true;
        
    }

// ========================================================================== //

?>