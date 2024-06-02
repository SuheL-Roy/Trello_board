require("./bootstrap");

window.Vue = require("vue");

// Import vue-toastification and its CSS
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

// Use vue-toastification with Vue
Vue.use(Toast, {
  position: "top-right",
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: true,
  closeButton: "button",
  icon: true,
  rtl: false
});
// Register our components
Vue.component("trello-board", require("./components/TrelloBoard.vue").default);

const app = new Vue({
  el: "#app"
});
