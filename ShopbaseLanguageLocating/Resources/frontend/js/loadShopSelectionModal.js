/**
 * Handle the modal box
 *
 * @param content
 * @param shops
 * @param titile
 */
function loadShopSelectionModal(content, shops, titile) {
    $.modal.open('<div class="language-locating-content"><div class="language--locating panelt"><div class="panel--body is--wide">' + content + '</div></div><div class="language--locating panelt"><div class="panel--body is--wide">' + shops + '</div></div></div>',{
        title: 'Select Shop',
        width: 450,
    });

    var offset = 21;
    var contentHeight = $('.language-locating-content').height();
    var modalHeight = $(".js--modal > .header").height() + contentHeight + offset;
    $(".js--modal").css('height', modalHeight);
    $(".js--modal > .content").css('height', contentHeight);
}

/**
 * Main process
 */
$(document).ready(function() {
    loadShopSelectionModal(ShopbaseLanguageLocatingContent, ShopbaseLanguageLocatingShops, ShopbaseLanugageLocatingTitle);

    $(".language-locating-content .select-field select").change(function() {
        var value = $(this).val().split("|");
        if(value[3] == 1) {
            document.location.href = '/';
        } else {
            document.location.href = value[0] + value[1] + value[2];
        }
    })
});
