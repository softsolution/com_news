{* ================================================================================ *}
{* ================= Редактирование/создание новости ============================== *}
{* ================================================================================ *}

<div class="con_heading">{$pagetitle}</div>

<form id="addform" name="addform" method="post" action="" enctype="multipart/form-data">
    <div class="bar" style="padding:15px 10px">
	<table width="605" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table width="700" border="0" cellspacing="5" class="proptable">
                        <tr>
                            <td width="230" valign="top">
                                <strong>Заголовок статьи:</strong><br />
                                <span class="hinttext">{$LANG.SHOW_ON_SITE}</span>
                            </td>
                            <td valign="top">
                                <input name="title" type="text" class="text-input" id="title" style="width:350px" value="{$mod.title|escape:'html'}" />
                            </td>
                        </tr>
                        <tr>
                            <td width="230" valign="top">
                                <strong>Источник: URL (с http://):</strong>
                            </td>
                            <td valign="top">
                                <input name="source_url" type="text" class="text-input" id="source_url" style="width:350px" value="{$mod.source_url}" />
                            </td>
                        </tr>
                        <tr>
                            <td width="230" valign="top">
                                <strong>Источник: Название:</strong><br />
                                <span class="hinttext">Если не указан, используется URL</span>
                            </td>
                            <td valign="top">
                                <input name="source_name" type="text" class="text-input" id="source_name" style="width:350px" value="{$mod.source_name}" />
                            </td>
                        </tr>
                        <tr>
                            <td valign="top">
                                <strong>Теги статьи:</strong><br />
                                <span class="hinttext">{$LANG.KEYWORDS_TEXT}</span>
                            </td>
                            <td valign="top">
                                <input name="tags" type="text" class="text-input" id="tags" style="width:350px" value="{$mod.tags|escape:'html'}" />
                                <script type="text/javascript">
                                    {$autocomplete_js}
                                </script>
                            </td>
                        </tr>
                        <tr>
                            <td valign="top" style="padding-top:8px">
                                    <strong>Фотография:</strong>
                            </td>
                            <td>
                            {if $mod.image}
                                <div style="padding-bottom:10px">
                                    <img src="/images/news/small/{$mod.image}" border="0" />
                                </div>
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="16"><input type="checkbox" id="delete_image" name="delete_image" value="1" /></td>
                                        <td><label for="delete_image">Удалить фотографию</label></td>
                                    </tr>
                                </table>
                            {/if}
                                <input type="file" name="picture" style="width:350px" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
	</table>
    </div>
    <table width="100%" border="0">
        <tr>
            <td>
                <h3>{$LANG.ARTICLE_ANNOUNCE}</h3>
                <div>{wysiwyg name='description' value=$mod.description height=200 width='100%'}</div>
                <h3>{$LANG.ARTICLE_TEXT}</h3>
                <div>{wysiwyg name='content' value=$mod.content height=450 width='100%'}</div>
            </td>
        </tr>
    </table>
<script type="text/javascript">
{literal}
    function submitArticle(){
        if (!$('input#title').val()){ core.alert('Укажите заголовок новости', 'Ошибка'); return false; }
        $('form#addform').submit();
    }
{/literal}
</script>

<p style="margin-top:15px">
<input name="add_mod" type="hidden" value="1" />
    <input name="savebtn" type="button" onclick="submitArticle()" id="add_mod" {if $do=='addarticle'} value="{$LANG.ADD_ARTICLE}" {else} value="{$LANG.SAVE_CHANGES}" {/if} />
    <input name="back" type="button" id="back" value="{$LANG.CANCEL}" onclick="window.history.back();"/>
    {if $do=='editarticle'}
        <input name="id" type="hidden" value="{$mod.id}" />
    {/if}
</p>
</form>
