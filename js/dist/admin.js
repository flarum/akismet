(()=>{var e={n:t=>{var a=t&&t.__esModule?()=>t.default:()=>t;return e.d(a,{a}),a},d:(t,a)=>{for(var r in a)e.o(a,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:a[r]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},t={};(()=>{"use strict";e.r(t);const a=flarum.core.compat["admin/app"];var r=e.n(a);r().initializers.add("flarum-akismet",(function(){r().extensionData.for("flarum-akismet").registerSetting({setting:"flarum-akismet.api_key",type:"text",label:r().translator.trans("flarum-akismet.admin.akismet_settings.api_key_label")}).registerSetting({setting:"flarum-akismet.delete_blatant_spam",type:"boolean",label:r().translator.trans("flarum-akismet.admin.akismet_settings.delete_blatant_spam_label"),help:r().translator.trans("flarum-akismet.admin.akismet_settings.delete_blatant_spam_help")}).registerPermission({icon:"fas fa-vote-yea",label:r().translator.trans("flarum-akismet.admin.permissions.bypass_akismet"),permission:"bypassAkismet"},"start")}))})(),module.exports=t})();
//# sourceMappingURL=admin.js.map