jQuery(document).ready(function($) {

    let color;
    let phone;
    let mail_svg;
    let whatsapp_svg;
    let admin_email

    $(".isw-wish-heart").click(function () {

        let data = collect_data($(this));
        $(this).addClass('isw-like-clicked');
        let cached_data = getCookie('isw-cached-wishlist', true);

        if (!verify_cache(cached_data, data.id)) {
            delete_from_cache(cached_data, data.id);
            $(this).removeClass('isw-like-clicked');
            return;
        }

        if(!is_empty_array(cached_data)){
            cached_data.push(data);
        } else {
            cached_data = [data];
        }

        setCookie('isw-cached-wishlist', cached_data);
    });

    $("#show-wishlist").click(function (){
        color = $(this).data('isw-color');
        phone = $(this).data('isw-phone');
        whatsapp_svg = $(this).data('isw-wasvg');
        mail_svg = $(this).data('isw-mailsvg');
        admin_email = $(this).data('isw-email');


        let cached_data = getCookie('isw-cached-wishlist', true);
        $("#isw-wishlist-popup").html(get_wishlist_template(cached_data));
        const inst = $('[data-remodal-id=isw-wishlist-popup]').remodal();
        inst.open();
    })

    function collect_data(btn) {
        return {
            id: btn.data('isw-wish-id'),
            img: btn.data('isw-wish-img'),
            capt: btn.data('isw-wish-capt'),
            likes: btn.data('isw-wish-likes'),
            comms: btn.data('isw-wish-comms'),
            link: btn.data('isw-wish-link'),
        }
    }

    function verify_cache(cached, cachable_id) {
        if(!is_empty_array(cached)) {
            for (let i = 0; i < cached.length; i++){
                if(cached[i].id === cachable_id){
                    return false;
                }
            }
            return true;
        } else {
            return true;
        }
    }

    function is_empty_array(arr){
        return !(typeof arr !== 'undefined' && arr.length > 0);
    }

    function delete_from_cache(cached_data, removable_item_id){
        for(let i = 0; i < cached_data.length; i++){
            if(cached_data[i].id == removable_item_id){
                cached_data.splice(i, 1);
                break;
            }
        }
        setCookie('isw-cached-wishlist', cached_data);
    }


    function get_wishlist_template (cached_data){
        let html;
        html = "<div class='isw-wishlist-popup'>"

        if(cached_data.length <= 0 ){
            html += "<div class='isw-wish-empty'>" +
                "<p>Wishlist is empty</p>" +
                "</div>"+
                "</div>";
            return html;

        }

        for (let i = 0; i < cached_data.length; i++){
            if($.isEmptyObject(cached_data[i])) continue;
            if(typeof(cached_data[i].capt) !== undefined && cached_data[i].capt !== "" ) cached_data[i].capt = cached_data[i].capt.slice(0,250) + "..."
            html += "<div class='isw-wishlist-popup-item'>"
            html += "<div class='isw-wishlist-popup-pic-container isw-inline-block'>"
            html += "<a href='" + cached_data[i].link +"' target='_blank'>";
            html += "<div class='isw-wishlist-popup-pic'>"
            html += "<img class='isw-wish-img' src='" + cached_data[i].img +"'/>"
            html += "</div>"
            html += "</a>"
            html += "</div>"
            html += "<div class='isw-wishlist-popup-info-container isw-inline-block'>"
            html += "<div class='isw-wishlist-popup-stats'>"
            html += "<span class='fa fa-heart'>&nbsp;</span>" + cached_data[i].likes
            html += "&nbsp;&nbsp;";
            html += "<span class='fa fa-comment'>&nbsp;</span>" + cached_data[i].comms
            html += "</div>"
            html += "<div class='isw-wishlist-popup-caption-container'>"
            html += "<p class='isw-wishlist-popup-caption'>" + cached_data[i].capt + "</p>"
            html += "</div>"
            html += "<div class='isw-wishlist-popup-buttons-container'>"
            html += '<div class="wis-inline wis-remodal-showcase-message" style="background-color: ' + color + '">'
            html += ' <a href="https://wa.me/' + phone + '?text=Hello! I want to buy this one ' +  cached_data[i].link + '" target="_blank">';
            html += '<img class="isw-center" style="height: 100%;" src="' + whatsapp_svg + '" alt=""/>';
            html += '</a>'
            html += "</div>"
            html += '<div class="wis-inline wis-remodal-showcase-message" style="margin-left: 5px;background-color: ' + color + '">';
            html += '<a href="mailto:' + admin_email + '?body=Hello! I want to buy this one ' + cached_data[i].link + '" target="_blank">';
            html += '<img class="isw-center" style="margin-top: 25%" src="' + mail_svg + '" alt=""/>';
            html += '</a>'
            html += '</div>';
            html += "</div>"
            html += "</div>"
            html += "</div>"
        }
        html += "</div>"
        return html;
    }



})
