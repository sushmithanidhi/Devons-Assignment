function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
      return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
      return uri + separator + key + "=" + value;
    }
  }

  function updatePage(pageNo)  {
    window.location.href = updateQueryStringParameter(window.location.href,'page',pageNo);
  }
  function paramExists(field){
    var url = window.location.href;
    if(url.indexOf('?' + field + '=') != -1)
        return true;
    else if(url.indexOf('&' + field + '=') != -1)
        return true;
    return false
  }
  $(document).ready(function() {
//     function myFunction() {
//         var input, filter, ul, li, a, i;
//         input = document.getElementById("mySearch");
//         filter = input.value.toUpperCase();
//         ul = document.getElementById("myMenu");
//         li = ul.getElementsByTagName("li");
//         for (i = 0; i < li.length; i++) {
//             a = li[i].getElementsByTagName("a")[0];
//             if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
//                 li[i].style.display = "";
//             } else {
//                 li[i].style.display = "none";
//             }
//         }
//     }
//
//     function openCity(evt, cityName) {
//         var i, tabcontent, tablinks;
//         tabcontent = document.getElementsByClassName("tabcontent");
//         for (i = 0; i < tabcontent.length; i++) {
//             tabcontent[i].style.display = "none";
//         }
//         tablinks = document.getElementsByClassName("tablinks");
//         for (i = 0; i < tablinks.length; i++) {
//             tablinks[i].className = tablinks[i].className.replace(" active", "");
//         }
//         document.getElementById(cityName).style.display = "block";
//         evt.currentTarget.className += " active";
//     }
//     if(window.location.pathname != $("#serverListUrl").val()){alert("clear cookies")
//         $.removeCookie('selectedIds',{path:'/'});
//     } else {
//         // alert($.cookie('selectedIds'));
//     }


    // $.each($.cookie, function(key, value) {
    //     $("#" + key).prop('checked', value);
    // });
    // if ($("input:checkbox:checked").length == 0 && !$.cookie('selectedIds'))
    // {
    //     $.removeCookie('selectedIds',{path:'/'});
    // }
    // $('input[type="checkbox"]').change(function() {
    //     var val = [];
    //     let cookieValue = $.cookie("selectedIds");
    //
    //     let currentId = this.value;
    //     if($(this).prop("checked")){
    //         $('.selectEntry:checked').each(function(i){
    //             val[i] = $(this).val();
    //             $.cookie("selectedIds",val);
    //             $.cookie(this.id,true);
    //         });
    //     }//alert($.cookie('selectedIds'));
    //     // let cookieArray = $.cookie("selectedIds").split(",");
    //     if(!($(this).prop("checked"))){
    //         $.removeCookie(this.id,{path: '/'});
    //         // if(cookieArray){
    //         //     cookieArray = $.grep(cookieArray, function(value) {
    //         //                     return value != currentId;
    //         //                 });
    //         // }
    //         $.cookie("selectedIds",cookieArray.join(","));
    //     }//alert(cookieArray.length);
    //     $('#compareData').prop('disabled', !(cookieArray.length >= 2));
    // })
    let loadFilterData;
    $('.clearCheckbox').click(function (){
        $.removeCookie("selectedIds");
        location.reload(true)
    })

    if(window.location.pathname != $("#serverListUrl").val()){
        $.removeCookie("selectedIds");
    }
    else {
        if($.cookie('selectedIds')){
        $('#compareData').prop('disabled', !($.cookie('selectedIds').split(",").length >= 2 && $.cookie('selectedIds').split(",").length < 7));
            if($.cookie('selectedIds').split(",").length > 6){
                document.getElementById("myPopup");
                popup.classList.toggle("show");
            }
        }
    }

    let cookieList = $.cookie('selectedIds') ? $.cookie('selectedIds').split(",") : [];

    $.each(cookieList, function(index,checkedIds){
        $("input[id="+checkedIds+"]").prop('checked',true);
    });

    $('input[type="checkbox"]').change(function() {
        let currentId = this.id;
        if($(this).prop("checked")) {
            cookieList.push(currentId);
            $.cookie('selectedIds', cookieList.join(','));
        }else{
            cookieList = $.grep(cookieList, function(value) {
                            return value != currentId;
                          });
            $.cookie('selectedIds', cookieList.join(','));
        }

        $('#compareData').prop('disabled', !($.cookie('selectedIds').split(",").length >= 2 && $.cookie('selectedIds').split(",").length < 7));
    });

    $('#compareData').click(function(){
        var selectedIds = $.cookie("selectedIds");
        var url = $('#compareDetailsUrl').val();
        $.ajax({
            url: url,
            type: 'post',
            data: {selectedIds: selectedIds},
            success: function(response){
                // $('#serverModal').modal('show');
                // Add response in Modal body
                $('.modal-body').html(response);

                // Display Modal
                $('#serverModal').modal('show');
            }
        });

    });

    $('#filterData').click(function() {
        var url = window.location.href;
        var filter = [];
        filter['ramFilter'] = $('#getRamFilter').val();
        filter['hddFilter'] = $('#getHDDFilter').val();
        filter['locationFilter'] = $('#getLocationFilter').val();
        // mail=$('#mailVal').val();
        var url = window.location.href;
        var filterKeys = ['ramFilter','hddFilter','locationFilter'];
        let append = '?';
        if(paramExists('page')){alert("yes");
            append = '&';
            url = updateQueryStringParameter(url,'page',1);
        }
        $.each(filterKeys, function(key, value) {
            if(paramExists(value)){
                url = updateQueryStringParameter(url,value,filter[value]);
            }else{
                url = url + append + value+'='+filter[value];
                append = '&';
            }
            });
        window.location.href = url;
    });

})