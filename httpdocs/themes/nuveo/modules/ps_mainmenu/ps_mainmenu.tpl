{assign var=_counter value=0}
{function name="menu" nodes=[] depth=0 parent=null}
    {if $nodes|count}
      <ul class="top-menu" {if $depth == 0}id="top-menu"{/if} data-depth="{$depth}">
        {foreach from=$nodes item=node}
            <li class="{$node.type}{if $node.current} current {/if} {if $node.image_urls}cat-thumb{/if}" id="{$node.page_identifier}">
            {assign var=_counter value=$_counter+1}
              <a
                class="{if $depth >= 0}dropdown-item{/if}{if $depth === 1} dropdown-submenu{/if}"
                href="{$node.url}" data-depth="{$depth}"
                {if $node.open_in_new_window} target="_blank" {/if}
              >
              {if $node.image_urls && $depth === 0}{foreach from=$node.image_urls item=image_url}<img class="category-thumbnail" src="{$image_url}" />{/foreach}{/if}                
                {if $node.children|count}
                  {* Cannot use page identifier as we can have the same page several times *}
                  {assign var=_expand_id value=10|mt_rand:100000}
                  <span class="float-xs-right hidden-lg-up">
                    <span data-target="#top_sub_menu_{$_expand_id}" data-toggle="collapse" class="navbar-toggler collapse-icons">
                      <i class="material-icons add">&#xE313;</i>
                      <i class="material-icons remove">&#xE316;</i>
                    </span>
                  </span>
                {/if}
                {$node.label}
              </a>
                {if $node.image_urls && $depth > 0}
                <div class="category-thumbnail">
                  {foreach from=$node.image_urls item=image_url}
                    <div>
                      <a class="menu-img-link" href="{$node.url}"><img class="lazyload" data-src="{$image_url}" /></a>
                    </div>  
                  {/foreach}
                </div>
                {/if}  
                {if $node.children|count}
                <div {if $depth === 0} class="popover sub-menu js-sub-menu collapse"{else} class="collapse"{/if} id="top_sub_menu_{$_expand_id}">
                  {menu nodes=$node.children depth=$node.depth parent=$node}                  
              </div>
              {/if}
            </li>
        {/foreach}
      </ul>
    {/if}
{/function}

<div class="menu-wrapper">
      <div class="menu clearfix js-top-menu hidden-lg-down" id="_desktop_top_menu">
  <div class="container">
        {menu nodes=$menu.children}
      <div class="clearfix"></div>
  </div>
  <div class="close_menu" id="close_menu" onclick="esconderMenu()">
    ▲
  </div>
  </div>
</div>
