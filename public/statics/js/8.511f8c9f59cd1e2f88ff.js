webpackJsonp([8],{"8iIn":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=null,s={data:function(){var t=this;return{modal1:!1,userName:"",value:"",model1:"",value1:"",page:1,cityList:[],columns:[{title:"投诉人",key:"userName"},{title:"被投诉人",key:"password"},{title:"投诉原因",key:"status"},{title:"投诉时间",key:"create_time"},{title:"Action",key:"action",width:150,align:"center",render:function(e,a){return e("div",[e("span",{props:{type:"primary",size:"small"},style:{marginRight:"5px",cursor:"pointer"},on:{click:function(){t.$api.go({that:t,url:"tosudetai",data:{id:a.row.id}})}}},"查看")])}}],data6:[]}},mounted:function(){i=this,this.pageinfo()},methods:{ok:function(){var t=this,e={userName:this.value,password:this.value1,role:this.model1};this.cityList.forEach(function(a){a.id==t.model1&&(e.role_title=a.title)}),this.$testdata(e)&&this.$api.adduser({data:e,success:function(t){i.pageinfo()}})},pageinfo:function(){this.$api.getuser({data:{current_page:this.page},success:function(t){console.log(t.data),i.data6=t.data.list}}),this.$api.getuserlist({data:{type:2,current_page:this.page},success:function(t){i.cityList=t.data.list}})},addUser:function(){this.modal1=!0},show:function(t){this.$Modal.info({title:"User Info",content:"Name："+this.data6[t].name+"<br>Age："+this.data6[t].age+"<br>Address："+this.data6[t].address})},remove:function(t){this.data6.splice(t,1)}}},l={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"hello"},[a("p",{staticClass:"page-title"},[t._v("当前位置：投诉管理")]),t._v(" "),a("div",{staticClass:"search"},[a("Input",{staticStyle:{width:"150px"},attrs:{placeholder:"投诉时间"},model:{value:t.userName,callback:function(e){t.userName=e},expression:"userName"}}),t._v(" "),a("Button",{attrs:{type:"primary"}},[t._v("搜索")])],1),t._v(" "),a("Table",{attrs:{border:"",columns:t.columns,data:t.data6}}),t._v(" "),a("Modal",{attrs:{width:"340",title:"添加账户"},on:{"on-ok":t.ok},model:{value:t.modal1,callback:function(e){t.modal1=e},expression:"modal1"}},[a("Input",{staticStyle:{width:"300px","margin-bottom":"10px"},attrs:{placeholder:"用户名"},model:{value:t.value,callback:function(e){t.value=e},expression:"value"}}),t._v(" "),a("Input",{staticStyle:{width:"300px","margin-bottom":"10px"},attrs:{type:"password",placeholder:"密码"},model:{value:t.value1,callback:function(e){t.value1=e},expression:"value1"}}),t._v(" "),a("Select",{staticStyle:{width:"300px","margin-bottom":"10px"},attrs:{clearable:"",placeholder:"选择角色"},model:{value:t.model1,callback:function(e){t.model1=e},expression:"model1"}},t._l(t.cityList,function(e){return a("Option",{key:e.id,attrs:{value:e.id}},[t._v(t._s(e.title))])}),1)],1)],1)},staticRenderFns:[]},o=a("VU/8")(s,l,!1,null,null,null);e.default=o.exports}});
//# sourceMappingURL=8.511f8c9f59cd1e2f88ff.js.map