(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-45b14176"],{"2e4d":function(t,e,a){"use strict";a("4d7e")},"4d7e":function(t,e,a){},7941:function(t,e,a){"use strict";a.r(e);var o=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("div",{staticClass:"de_tab tab_simple"},[a("ul",{staticClass:"de_nav row"},[a("li",[a("b-button",{staticClass:"tab_view",class:{"btn-primary":"sale"==t.tab},staticStyle:{width:"120px",padding:"8px 15px"},attrs:{variant:"secondary",to:"/profile/"+this.$route.params.address+"/nfts/sale"}},[t._v(t._s(t.$t("collection.sale")))])],1),a("li",[a("b-button",{staticClass:"tab_view",class:{"btn-primary":"owner"==t.tab},staticStyle:{width:"120px",padding:"8px 15px"},attrs:{variant:"secondary",to:"/profile/"+this.$route.params.address+"/nfts/owner"}},[t._v(t._s(t.$t("collection.holding")))])],1),a("li",[a("b-button",{staticClass:"tab_view",class:{"btn-primary":"creator"==t.tab},staticStyle:{width:"120px",padding:"8px 15px"},attrs:{variant:"secondary",to:"/profile/"+this.$route.params.address+"/nfts/creator"}},[t._v(t._s(t.$t("collection.created")))])],1),this.$route.params.address==this.$wallet.getWallet().getAccount()?a("li",[a("b-button",{staticClass:"tab_view",staticStyle:{width:"120px",padding:"8px 15px"},attrs:{variant:"secondary",to:"/profile/"+this.$route.params.address+"/setting"}},[t._v(t._s(t.$t("collection.setting")))])],1):t._e(),this.$route.params.address!=this.$wallet.getWallet().getAccount()||t.collectionData.auth?t._e():a("li",[a("b-button",{staticClass:"tab_view",staticStyle:{width:"120px",padding:"8px 15px"},attrs:{variant:"secondary",to:"/profile/"+this.$route.params.address+"/auth"}},[t._v(t._s(t.$t("collection.apply")))])],1)]),a("div",{staticClass:"pt-4 pb-4",attrs:{id:"profile1_content"}},[a("div",[a("b-form",{on:{submit:t.profileSaveSubmit}},[a("b-row",[a("b-col",{staticClass:"col-12 mb-4"},[a("label",{staticClass:"form-label d-block",attrs:{for:"exampleFormControlInput1"}},[t._v(t._s(t.$t("collection.name")))]),a("input",{directives:[{name:"model",rawName:"v-model",value:t.formData.name,expression:"formData.name"}],staticClass:"form-control w-100  p-2",attrs:{type:"text",id:"exampleFormControlInput1",placeholder:t.$t("collection.enter_name")},domProps:{value:t.formData.name},on:{input:function(e){e.target.composing||t.$set(t.formData,"name",e.target.value)}}})]),a("b-col",{staticClass:"col-12 mb-4"},[a("label",{staticClass:"form-label d-block",attrs:{for:"exampleFormControlInput1_2"}},[t._v(t._s(t.$t("collection.phone")))]),a("input",{directives:[{name:"model",rawName:"v-model",value:t.formData.phone,expression:"formData.phone"}],staticClass:"form-control w-100  p-2",attrs:{type:"text",id:"exampleFormControlInput1_2",placeholder:t.$t("collection.enter_phone")},domProps:{value:t.formData.phone},on:{input:function(e){e.target.composing||t.$set(t.formData,"phone",e.target.value)}}})]),a("b-col",{staticClass:"col-12 mb-4"},[a("label",{staticClass:"form-label d-block",attrs:{for:"exampleFormControlInput1_3"}},[t._v(t._s(t.$t("collection.email")))]),a("input",{directives:[{name:"model",rawName:"v-model",value:t.formData.email,expression:"formData.email"}],staticClass:"form-control w-100  p-2",attrs:{type:"email",id:"exampleFormControlInput1_3",placeholder:t.$t("collection.enter_email")},domProps:{value:t.formData.email},on:{input:function(e){e.target.composing||t.$set(t.formData,"email",e.target.value)}}})]),a("b-col",{staticClass:"col-12 mb-4"},[a("textarea",{directives:[{name:"model",rawName:"v-model",value:t.formData.description,expression:"formData.description"}],staticClass:"form-control w-100  p-2",staticStyle:{outline:"0",border:"1px solid #F2F2F2","background-color":"#F7F7F7","font-size":"14px",height:"200px"},attrs:{id:"exampleFormControlTextarea1",rows:"3",placeholder:t.$t("collection.enter_introduction")},domProps:{value:t.formData.description},on:{input:function(e){e.target.composing||t.$set(t.formData,"description",e.target.value)}}})]),a("b-col",{staticClass:"col-12"},[a("b-button",{staticClass:"d-block m-auto",class:{disabled:t.loading.profileSubmit},staticStyle:{height:"44px",width:"120px"},attrs:{variant:"primary",type:"submit"}},[t.loading.profileSubmit?a("b-spinner"):a("span",[t._v(t._s(t.$t("collection.apply")))])],1)],1)],1)],1)],1)])])])},r=[],i=(a("8e6e"),a("ac6a"),a("456d"),a("7f7f"),a("96cf"),a("1da1")),n=a("ade3"),s=a("2f62");function l(t,e){var a=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),a.push.apply(a,o)}return a}function c(t){for(var e=1;e<arguments.length;e++){var a=null!=arguments[e]?arguments[e]:{};e%2?l(Object(a),!0).forEach((function(e){Object(n["a"])(t,e,a[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(a)):l(Object(a)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(a,e))}))}return t}var p={computed:c({},Object(s["c"])(["getMyCollection"])),props:{collectionData:{type:Object}},watch:{collectionData:function(t){this.changeCollectionData(t)}},data:function(){return{loading:{profileSubmit:!1},formData:{name:"",nick:"",phone:"",description:""}}},methods:{changeCollectionData:function(t){},profileSaveSubmit:function(t){t.preventDefault(),this.profileSave()},profileSave:function(){var t=Object(i["a"])(regeneratorRuntime.mark((function t(){var e;return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:if(1!=this.loading.profileSubmit){t.next=2;break}return t.abrupt("return");case 2:return this.loading.profileSubmit=!0,t.next=5,this.$api.applyAuth({data:{name:this.formData.name,phone:this.formData.phone,email:this.formData.email,description:this.formData.description}});case 5:if(e=t.sent,this.loading.profileSubmit=!1,0!=e){t.next=11;break}return t.abrupt("return");case 11:if(void 0==e.error){t.next=14;break}return alert(e.error.message),t.abrupt("return");case 14:alert(this.$t("collection.apply_alert")),this.loading.profileSubmit=!1;case 16:case"end":return t.stop()}}),t,this)})));function e(){return t.apply(this,arguments)}return e}()},created:function(){document.title=this.$t("collection.apply_title")+" | "+this.$env.VUE_APP_NAME,void 0!=this.collectionData&&this.changeCollectionData(this.collectionData)}},m={name:"AuthApply",mixins:[p]},u=m,d=(a("2e4d"),a("2877")),f=Object(d["a"])(u,o,r,!1,null,"4d516f6c",null);e["default"]=f.exports}}]);