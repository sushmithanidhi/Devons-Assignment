$(document).ready(function() {
    // $('#storeRange').change(function (){
    //     $('#storageText').val(this.value + 'TB');
    // })
    // $('.clearCheckbox').click(function (){
    //     $.removeCookie("selectedIds");
    //     location.reload(true)
    // })

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
                $('.modal-body').html(response);
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
        var url = window.location.href;
        var filterKeys = ['ramFilter','hddFilter','locationFilter'];
        let append = '?';
        if(paramExists('page')){
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