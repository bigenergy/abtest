{if $paginator->lastPage > 1}
    <nav class="pagination" aria-label="Страницы">
        {if $paginator->hasPrevious()}
            <a class="pagination__item" href="{$paginator->url($paginator->page - 1)}" rel="prev" aria-label="Предыдущая страница">←</a>
        {/if}

        {foreach $paginator->links() as $link}
            {if $link === null}
                <span class="pagination__gap">…</span>
            {elseif $link === $paginator->page}
                <span class="pagination__item pagination__item--current" aria-current="page">{$link}</span>
            {else}
                <a class="pagination__item" href="{$paginator->url($link)}">{$link}</a>
            {/if}
        {/foreach}

        {if $paginator->hasNext()}
            <a class="pagination__item" href="{$paginator->url($paginator->page + 1)}" rel="next" aria-label="Следующая страница">→</a>
        {/if}
    </nav>
{/if}
