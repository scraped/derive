<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <gmap-map
                    :center="center"
                    :zoom="7"
                    map-type-id="terrain"
                    style="width: 500px; height: 300px; margin: 0 auto;">
                    <gmap-marker :position="marker">
                    </gmap-marker>
                </gmap-map>
            </div>
            <div class="col-md-8 col-md-offset-2" style="padding-top:20px">
                <div class="form-inline">
                    <div class="form-group">
                        <input type="button" class="btn btn-primary form-control" value="Go" v-on:click="search">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import * as VueGoogleMaps from 'vue2-google-maps';
    import Vue from 'vue';

    Vue.use(VueGoogleMaps, {
        load: {
            key: 'AIzaSyDBBuh9aUBOJuO0yGB0EDaSz5HmD8lnXKY',
            libraries: 'places'
        }
    });

    export default {
        data() {
            var center = {lat: 10.0, lng: 10.0};
            var marker = {lat: 10.0, lng: 10.0};
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    center.lat = position.coords.latitude;
                    center.lng = position.coords.longitude;
                    marker.lat = position.coords.latitude;
                    marker.lng = position.coords.longitude;
                });
            }

            return {
                center,
                marker
            };
        },

        methods: {
            search() {

            }
        }
    }
</script>
