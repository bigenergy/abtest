{extends file='layout.tpl'}

{block name=title}{$title} — {$smarty.block.parent}{/block}

{block name=content}
    <div class="error-page">
        <p class="error-page__code">{$code}</p>
        <h1 class="error-page__title">{$title}</h1>
        <p class="error-page__text">{$text}</p>
        <a class="button" href="/">На главную</a>
    </div>
{/block}
