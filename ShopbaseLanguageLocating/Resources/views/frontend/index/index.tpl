{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_page_wrap" prepend}
    {if $isLanguageLocatingShopSelection}
        <script>
            // Generate the script content
            var ShopbaseLanguageLocatingContent = "{s namespace='frontend/plugins/Shopbase/LanguageLocating' name='ModalText'}Sorry, this shop is not supporting your language. Please select the shop with which you want to continue.{/s}";
            var ShopbaseLanguageLocatingShops = '<div class="select-field"><select id="shopbaseLanguageLocatingShopSelection"><option>{s namespace="frontend/plugins/Shopbase/LanguageLocating" name="ModalSelectDefault"}Please select{/s}</option>{foreach key=key item=value from=$languageLocatingShops}<option value="{$value["host"]}|{$value["base_path"]}|{$value["base_url"]}|{$value["default"]}">{$value["name"]} ({s namespace="frontend/plugins/Shopbase/LanguageLocating" name="ModalSelectLangText"}Language{/s}: {$value["language"]})</option>{/foreach}</select></div>';
            var ShopbaseLanugageLocatingTitle = "{s namespace='frontend/plugins/Shopbase/LanguageLocating' name='ModalTitle'}Select your shop.{/s}";
        </script>
    {/if}
{/block}
