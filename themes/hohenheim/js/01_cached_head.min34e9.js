!function(exports,global){global["true"]=exports,window.Modernizr=function(a,b,c){function d(a){s.cssText=a}function e(a,b){return typeof a===b}function f(a,b){return!!~(""+a).indexOf(b)}function g(a,b){for(var d in a){var e=a[d];if(!f(e,"-")&&s[e]!==c)return"pfx"==b?e:!0}return!1}function h(a,b,d){for(var f in a){var g=b[a[f]];if(g!==c)return d===!1?a[f]:e(g,"function")?g.bind(d||b):g}return!1}function i(a,b,c){var d=a.charAt(0).toUpperCase()+a.slice(1),f=(a+" "+v.join(d+" ")+d).split(" ");return e(b,"string")||e(b,"undefined")?g(f,b):(f=(a+" "+w.join(d+" ")+d).split(" "),h(f,b,c))}var j,k,l,m="2.6.3",n={},o=!0,p=b.documentElement,q="modernizr",r=b.createElement(q),s=r.style,t=({}.toString," -webkit- -moz- -o- -ms- ".split(" ")),u="Webkit Moz O ms",v=u.split(" "),w=u.toLowerCase().split(" "),x={svg:"http://www.w3.org/2000/svg"},y={},z=[],A=z.slice,B=function(a,c,d,e){var f,g,h,i,j=b.createElement("div"),k=b.body,l=k||b.createElement("body");if(parseInt(d,10))for(;d--;)h=b.createElement("div"),h.id=e?e[d]:q+(d+1),j.appendChild(h);return f=["&#173;",'<style id="s',q,'">',a,"</style>"].join(""),j.id=q,(k?j:l).innerHTML+=f,l.appendChild(j),k||(l.style.background="",l.style.overflow="hidden",i=p.style.overflow,p.style.overflow="hidden",p.appendChild(l)),g=c(j,a),k?j.parentNode.removeChild(j):(l.parentNode.removeChild(l),p.style.overflow=i),!!g},C=function(b){var c=a.matchMedia||a.msMatchMedia;if(c)return c(b).matches;var d;return B("@media "+b+" { #"+q+" { position: absolute; } }",function(b){d="absolute"==(a.getComputedStyle?getComputedStyle(b,null):b.currentStyle).position}),d},D={}.hasOwnProperty;l=e(D,"undefined")||e(D.call,"undefined")?function(a,b){return b in a&&e(a.constructor.prototype[b],"undefined")}:function(a,b){return D.call(a,b)},Function.prototype.bind||(Function.prototype.bind=function(a){var b=this;if("function"!=typeof b)throw new TypeError;var c=A.call(arguments,1),d=function(){if(this instanceof d){var e=function(){};e.prototype=b.prototype;var f=new e,g=b.apply(f,c.concat(A.call(arguments)));return Object(g)===g?g:f}return b.apply(a,c.concat(A.call(arguments)))};return d}),y.touch=function(){var c;return"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch?c=!0:B(["@media (",t.join("touch-enabled),("),q,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(a){c=9===a.offsetTop}),c},y.csstransforms=function(){return!!i("transform")},y.csstransforms3d=function(){var a=!!i("perspective");return a&&"webkitPerspective"in p.style&&B("@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}",function(b){a=9===b.offsetLeft&&3===b.offsetHeight}),a},y.svg=function(){return!!b.createElementNS&&!!b.createElementNS(x.svg,"svg").createSVGRect};for(var E in y)l(y,E)&&(k=E.toLowerCase(),n[k]=y[E](),z.push((n[k]?"":"no-")+k));return n.addTest=function(a,b){if("object"==typeof a)for(var d in a)l(a,d)&&n.addTest(d,a[d]);else{if(a=a.toLowerCase(),n[a]!==c)return n;b="function"==typeof b?b():b,"undefined"!=typeof o&&o&&(p.className+=" "+(b?"":"no-")+a),n[a]=b}return n},d(""),r=j=null,n._version=m,n._prefixes=t,n._domPrefixes=w,n._cssomPrefixes=v,n.mq=C,n.testProp=function(a){return g([a])},n.testAllProps=i,n.testStyles=B,p.className=p.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(o?" js "+z.join(" "):""),n}(this,this.document),function(a,b){function c(a,b){var c=a.createElement("p"),d=a.getElementsByTagName("head")[0]||a.documentElement;return c.innerHTML="x<style>"+b+"</style>",d.insertBefore(c.lastChild,d.firstChild)}function d(){var a=w.elements;return"string"==typeof a?a.split(" "):a}function e(a){var b=v[a[t]];return b||(b={},u++,a[t]=u,v[u]=b),b}function f(a,c,d){if(c||(c=b),p)return c.createElement(a);d||(d=e(c));var f;return f=d.cache[a]?d.cache[a].cloneNode():s.test(a)?(d.cache[a]=d.createElem(a)).cloneNode():d.createElem(a),f.canHaveChildren&&!r.test(a)?d.frag.appendChild(f):f}function g(a,c){if(a||(a=b),p)return a.createDocumentFragment();c=c||e(a);for(var f=c.frag.cloneNode(),g=0,h=d(),i=h.length;i>g;g++)f.createElement(h[g]);return f}function h(a,b){b.cache||(b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag()),a.createElement=function(c){return w.shivMethods?f(c,a,b):b.createElem(c)},a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+d().join().replace(/\w+/g,function(a){return b.createElem(a),b.frag.createElement(a),'c("'+a+'")'})+");return n}")(w,b.frag)}function i(a){a||(a=b);var d=e(a);return w.shivCSS&&!o&&!d.hasCSS&&(d.hasCSS=!!c(a,"article,aside,figcaption,figure,footer,header,hgroup,nav,section{display:block}mark{background:#FF0;color:#000}")),p||h(a,d),a}function j(a){for(var b,c=a.getElementsByTagName("*"),e=c.length,f=RegExp("^(?:"+d().join("|")+")$","i"),g=[];e--;)b=c[e],f.test(b.nodeName)&&g.push(b.applyElement(k(b)));return g}function k(a){for(var b,c=a.attributes,d=c.length,e=a.ownerDocument.createElement(y+":"+a.nodeName);d--;)b=c[d],b.specified&&e.setAttribute(b.nodeName,b.nodeValue);return e.style.cssText=a.style.cssText,e}function l(a){for(var b,c=a.split("{"),e=c.length,f=RegExp("(^|[\\s,>+~])("+d().join("|")+")(?=[[\\s,>+~#.:]|$)","gi"),g="$1"+y+"\\:$2";e--;)b=c[e]=c[e].split("}"),b[b.length-1]=b[b.length-1].replace(f,g),c[e]=b.join("}");return c.join("{")}function m(a){for(var b=a.length;b--;)a[b].removeNode()}function n(a){function b(){clearTimeout(g._removeSheetTimer),d&&d.removeNode(!0),d=null}var d,f,g=e(a),h=a.namespaces,i=a.parentWindow;return!z||a.printShived?a:("undefined"==typeof h[y]&&h.add(y),i.attachEvent("onbeforeprint",function(){b();for(var e,g,h,i=a.styleSheets,k=[],m=i.length,n=Array(m);m--;)n[m]=i[m];for(;h=n.pop();)if(!h.disabled&&x.test(h.media)){try{e=h.imports,g=e.length}catch(o){g=0}for(m=0;g>m;m++)n.push(e[m]);try{k.push(h.cssText)}catch(o){}}k=l(k.reverse().join("")),f=j(a),d=c(a,k)}),i.attachEvent("onafterprint",function(){m(f),clearTimeout(g._removeSheetTimer),g._removeSheetTimer=setTimeout(b,500)}),a.printShived=!0,a)}var o,p,q=a.html5||{},r=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,s=/^<|^(?:a|b|button|code|div|fieldset|form|h1|h2|h3|h4|h5|h6|i|iframe|img|input|label|li|link|ol|option|p|param|q|script|select|span|strong|style|table|tbody|td|textarea|tfoot|th|thead|tr|ul)$/i,t="_html5shiv",u=0,v={};!function(){try{var a=b.createElement("a");a.innerHTML="<xyz></xyz>",o="hidden"in a,p=1==a.childNodes.length||function(){b.createElement("a");var a=b.createDocumentFragment();return"undefined"==typeof a.cloneNode||"undefined"==typeof a.createDocumentFragment||"undefined"==typeof a.createElement}()}catch(c){o=!0,p=!0}}();var w={elements:q.elements||"abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video",shivCSS:q.shivCSS!==!1,supportsUnknownElements:p,shivMethods:q.shivMethods!==!1,type:"default",shivDocument:i,createElement:f,createDocumentFragment:g};a.html5=w,i(b);var x=/^$|\b(?:all|print)\b/,y="html5shiv",z=!p&&function(){var c=b.documentElement;return"undefined"!=typeof b.namespaces&&"undefined"!=typeof b.parentWindow&&"undefined"!=typeof c.applyElement&&"undefined"!=typeof c.removeNode&&"undefined"!=typeof a.attachEvent}();w.type+=" print",w.shivPrint=n,n(b)}(this,document),"object"!=typeof JSON&&(JSON={}),function(){"use strict";function f(a){return 10>a?"0"+a:a}function quote(a){return escapable.lastIndex=0,escapable.test(a)?'"'+a.replace(escapable,function(a){var b=meta[a];return"string"==typeof b?b:"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+a+'"'}function str(a,b){var c,d,e,f,g,h=gap,i=b[a];switch(i&&"object"==typeof i&&"function"==typeof i.toJSON&&(i=i.toJSON(a)),"function"==typeof rep&&(i=rep.call(b,a,i)),typeof i){case"string":return quote(i);case"number":return isFinite(i)?String(i):"null";case"boolean":case"null":return String(i);case"object":if(!i)return"null";if(gap+=indent,g=[],"[object Array]"===Object.prototype.toString.apply(i)){for(f=i.length,c=0;f>c;c+=1)g[c]=str(c,i)||"null";return e=0===g.length?"[]":gap?"[\n"+gap+g.join(",\n"+gap)+"\n"+h+"]":"["+g.join(",")+"]",gap=h,e}if(rep&&"object"==typeof rep)for(f=rep.length,c=0;f>c;c+=1)"string"==typeof rep[c]&&(d=rep[c],e=str(d,i),e&&g.push(quote(d)+(gap?": ":":")+e));else for(d in i)Object.prototype.hasOwnProperty.call(i,d)&&(e=str(d,i),e&&g.push(quote(d)+(gap?": ":":")+e));return e=0===g.length?"{}":gap?"{\n"+gap+g.join(",\n"+gap)+"\n"+h+"}":"{"+g.join(",")+"}",gap=h,e}}"function"!=typeof Date.prototype.toJSON&&(Date.prototype.toJSON=function(){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+f(this.getUTCMonth()+1)+"-"+f(this.getUTCDate())+"T"+f(this.getUTCHours())+":"+f(this.getUTCMinutes())+":"+f(this.getUTCSeconds())+"Z":null},String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(){return this.valueOf()});var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={"\b":"\\b","	":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},rep;"function"!=typeof JSON.stringify&&(JSON.stringify=function(a,b,c){var d;if(gap="",indent="","number"==typeof c)for(d=0;c>d;d+=1)indent+=" ";else"string"==typeof c&&(indent=c);if(rep=b,b&&"function"!=typeof b&&("object"!=typeof b||"number"!=typeof b.length))throw new Error("JSON.stringify");return str("",{"":a})}),"function"!=typeof JSON.parse&&(JSON.parse=function(text,reviver){function walk(a,b){var c,d,e=a[b];if(e&&"object"==typeof e)for(c in e)Object.prototype.hasOwnProperty.call(e,c)&&(d=walk(e,c),void 0!==d?e[c]=d:delete e[c]);return reviver.call(a,b,e)}var j;if(text=String(text),cx.lastIndex=0,cx.test(text)&&(text=text.replace(cx,function(a){return"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})),/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"")))return j=eval("("+text+")"),"function"==typeof reviver?walk({"":j},""):j;throw new SyntaxError("JSON.parse")})}()}({},function(){return this}());