
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example-component', require('./components/ExampleComponent.vue'));
Vue.component('desk-component', require('./components/DeskComponent.vue'));
Vue.component('findgame-component', require('./components/FindGameComponent.vue'));
Vue.component('roundresults-component', require('./components/RoundResults.vue'));
Vue.component('gameend-component', require('./components/GameEnd.vue'));

const app = new Vue({
    el: '#app'
});
