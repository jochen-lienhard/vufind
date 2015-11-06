$(document).ready(function(){
        var prefix="py_facet";
        var pubyear=[];
        var pubcount=[];
        var summcount=0;
        $('#pubdate_block div').hide();
        $('#pubdate_block div').each(function(i) {
           var myYear = $(this).attr('data-year');
           var myCount = $(this).attr('data-count');
           if (myYear != "0") {
               pubyear.push(myYear);
               pubcount.push(myCount);
               summcount+=parseInt(myCount);
           } else {
               $('#pubdate_block div[data-year="0"]').show();
           }
       });

        var loccount=0;
        var block = parseInt(summcount / 9);
        var zahlcounter = 0;
        var oldname = 0;
        for (var i=0; i<pubcount.length; i++) {
                if (i < pubcount.length-1) {
                        if (loccount < block) {
                                loccount += parseInt(pubcount[i]);
                        } else {
                                loccount += parseInt(pubcount[i]);
                                zahlcounter += 1;
                                if (zahlcounter == 1) {
                                        $('#pubdate_block').append("<div class='list-group-item'><a href='javascript:document."+prefix+"Filter."+prefix+"to.value="+pubyear[i]+";document."+prefix+"Filter.submit();'>vor "+pubyear[i]+"</a><span class='badge'> ("+loccount+") </span></div>");
                                } else {
                                        $('#pubdate_block').append("<div class='list-group-item'><a href='javascript:document."+prefix+"Filter."+prefix+"from.value="+oldname+";document."+prefix+"Filter."+prefix+"to.value="+pubyear[i]+";document."+prefix+"Filter.submit();'>"+oldname+" - "+pubyear[i]+"</a><span class='badge'> ("+loccount+") </span></div>");
                                }
                                loccount = 0;
                                oldname = parseInt(pubyear[i])+1;
                        }
                } else {
                        loccount += parseInt(pubcount[i]);
                        $('#pubdate_block').append("<div class='list-group-item'><a href='javascript:document."+prefix+"Filter."+prefix+"from.value="+oldname+";document."+prefix+"Filter.submit();'>"+oldname+" bis ...</a><span class='badge'> ("+loccount+") </span></div>");
                }
        }
});
