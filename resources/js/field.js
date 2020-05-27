Nova.booting((Vue, router, store) => {
  Vue.component('index-route-map-field', require('./components/IndexField'))
  Vue.component('detail-route-map-field', require('./components/DetailField'))
  Vue.component('form-route-map-field', require('./components/FormField'))
})
