$(function(){
  $(".linktrack").click(function(){
    fbq(
      "trackCustom", "ButtonClick", {
        ClickUrl: location.pathname,
        ClickType: $(this).attr("data-link")
      }
    );

    fbq(
      "trackCustom", "AllConversion", {
        ClickUrl: location.pathname
      }
    );

    dtLyr.push({
      "ev_category": "ButtonClick",
      "ev_action": location.pathname,
      "ev_label": $(this).attr("data-link"),
      "event": "ua_event"
    });

    if(isset($(this).attr("target")) && $(this).attr("target") == "_blank"){
      return true;
    }else{
      setTimeout("location.href = '" + $(this).attr("href") + "'",200);
      return false;
    }
  });

  function isset(data){
    return(typeof(data) != "undefined");
  }
});

