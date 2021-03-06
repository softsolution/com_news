{* ================================================================================ *}
{* ========================= Просмотр новостей ==================================== *}
{* ================================================================================ *}

{if $cfg.showrss}
    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td>
                <h1 class="con_heading">{$pagetitle}</h1>
            </td>
            <td valign="top" style="padding-left:6px">
                <div class="con_rss_icon">
                    <a href="/rss/news/all/feed.rss" title="{$LANG.RSS}"><img src="/templates/{template}/images/icons/rss.png" border="0" alt="{$LANG.RSS}"/></a>
                </div>
            </td>
        </tr>
    </table>
{else}
    <h1 class="con_heading">{$pagetitle}</h1>
{/if}

{if $articles}
	{assign var="col" value="1"}	
	<table class="contentlist" cellspacing="2" border="0" width="100%">
            {foreach key=tid item=article from=$articles}
            {if $col==1} <tr> {/if}
                <td width="20" valign="top">
                    <img src="/templates/{template}/images/icons/article.png" border="0" class="con_icon"/>
                </td>
                <td width="" valign="top">
                    <div class="con_title">
                        <a href="{$article.url}" class="con_titlelink">{$article.title}</a>
                    </div>

                    <div class="con_desc">
                        {if $article.image}
                            <div class="con_image">
                                <img src="/images/news/small/{$article.image}" border="0" alt="{$article.title|escape:'html'}"/>
                            </div>
                        {/if}
                        {$article.description}
                    </div>

                    {if $cfg.showcomm || $cfg.showdate || ($cfg.showtags && $article.tagline)}
                        <div class="con_details">
                            {if $cfg.showdate}
                                {$article.fpubdate} - <a href="{profile_url login=$article.user_login}" style="color:#666">{$article.author}</a>
                            {/if}
                            {if $cfg.showcomm}
                                {if $cfg.showdate} | {/if}
                                <a href="{$article.url}" title="{$LANG.DETAIL}">{$LANG.DETAIL}</a>
                                | <a href="{$article.url}#c" title="{$LANG.COMMENTS}">{$article.comments|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}</a>
                            {/if}
                             | {$article.hits|spellcount:$LANG.HIT:$LANG.HIT2:$LANG.HIT10}
                            {if $cfg.showtags && $article.tagline}
                                {if $cfg.showdate || $cfg.showcomm} <br/> {/if}
                                {if $article.tagline} <strong>{$LANG.TAGS}:</strong> {$article.tagline} {/if}
                            {/if}
                        </div>
                    {/if}					
                </td>
                {if $col==$cfg.maxcols} </tr> {assign var="col" value="1"} {else} {math equation="x + 1" x=$col assign="col"} {/if}
		{/foreach}
		{if $col>1} 
			<td colspan="{math equation="x - y + 1" x=$col y=$cfg.maxcols}">&nbsp;</td></tr>
		{/if}
	</table>
	{$pagebar}
        
{else}
    Нет новостей
{/if}