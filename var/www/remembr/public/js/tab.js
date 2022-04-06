// JavaScript Document

$(document).ready(function() {
   
    $(function(arg) {
      //tab
     
      $(".top_info_tab ul li a").each(function(i){ 
       i=i+1;       
       $(this).click(function(){
        $(".top_info_tab ul li a").removeClass("tab_act");      
        $(".tabDetails").hide();
        $("#tabCont_"+i).show();
        $(this).addClass("tab_act");
        return false;
       });
      });
    })

    $(function(arg) {
      //tab
     
      $(".info_tab_thumb2 ul li a").each(function(i){ 
       i=i+1;       
       $(this).click(function(){
        $(".info_tab_thumb2 ul li a").removeClass("tab_act2");      
        $(".tabDetails2").hide();
        $("#tabCont2_"+i).show();
        $(this).addClass("tab_act2");
        return false;
       });
      });
    })

    $(function(arg) {
      //tab
     
      $(".light_thumb_nav ul li a").each(function(i){ 
       i=i+1;       
       $(this).click(function(){
        $(".light_thumb_nav ul li a").removeClass("tab_act3");      
        $(".light_tabdetails").hide();
        $("#light_tabcont_"+i).show();
        $(this).addClass("tab_act3");
        return false;
       });
      });
    })
   
});
