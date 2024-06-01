require("./bootstrap");

window.Vue = require("vue");

// Register our components
Vue.component("trello-board", require("./components/TrelloBoard.vue").default);

const app = new Vue({
  el: "#app"
});
