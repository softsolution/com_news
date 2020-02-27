{* ================================================================================ *}
{* ================================ Просмотр новости =============================== *}
{* ================================================================================ *}

{* ======================= Заголовок новости =============================== *}

<h1 class="con_heading">{$article.title}</h1>

{* ======================= Источник =============================== *}
{if $article.showsource && ($article.source_url || $article.source_name)}
    <div class="con_pubdate">
        Источник: 
        {if $article.source_url}<a href="/go/url={$article.source_url}">{/if}
            {if $article.source_name}{$article.source_name}{else}{$article.source_url}{/if}
        {if $article.source_url}</a>{/if}
    </div>
{/if}


{* ======================= Дата публикации =============================== *}
{if $article.showdate}
    <div class="con_pubdate">
            {if !$article.published}<span style="color:#CC0000">{$LANG.NO_PUBLISHED}</span>{else}{$article.pubdate}{/if} - <a href="{profile_url login=$article.user_login}">{$article.author}</a>
    </div>
{/if}



{* =============== Текст статьи =============================== *}
<div class="con_text" style="overflow:hidden">
    {if $article.image}
        <div class="con_image" style="float:left;margin-top:10px;margin-right:20px;margin-bottom:20px">
            <img src="/images/news/medium/{$article.image}" border="0" alt="{$article.image}"/>
        </div>
    {/if}
    {$article.content}
</div>

{* ============= Ссылки редактирования и модерации ======================== *}
{if $is_admin || $is_editor || $is_author}
    <div class="blog_comments">
        {if !$article.published && ($is_admin || $is_editor)}
        	<a class="blog_moderate_yes" href="/news/publish{$article.id}.html">{$LANG.ARTICLE_ALLOW}</a> |
        {/if}
        {if $is_admin || $is_editor || $is_author_del}
        	<a class="blog_moderate_no" href="/news/delete{$article.id}.html">{$LANG.DELETE}</a> |
        {/if}
        {if $is_admin || $is_editor || $is_author}
        	<a href="/news/edit{$article.id}.html" class="blog_entry_edit">{$LANG.EDIT}</a>
        {/if}
    </div>
{/if}

{* ================ Теги статьи =============================== *}
{if $cfg.showtags}
    {$tagbar}
{/if}