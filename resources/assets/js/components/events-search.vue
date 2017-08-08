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
        <div class="row" v-if="geoError">
            <div class="col-md-8 col-md-offset-2">
                TEST
            </div>
        </div>
    </div>
</template>

<script>
    import * as VueGoogleMaps from 'vue2-google-maps';
    import Vue from 'vue';

    const moment = require('moment');

    Vue.use(VueGoogleMaps, {
        load: {
            key: 'AIzaSyDBBuh9aUBOJuO0yGB0EDaSz5HmD8lnXKY',
            libraries: 'places'
        }
    });

    export default {
        components: { VueGoogleAutocomplete },

        data() {
            var center = {lat: 10.0, lng: 10.0};
            var marker = {lat: 10.0, lng: 10.0};

            return {
                center,
                marker,
                enabled: false,
                geoError: false
            };
        },

        mounted() {
            if (!navigator.geolocation) {
                this.enabled = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.$set(this.center, 'lat', position.coords.latitude);
                    this.$set(this.center, 'lng', position.coords.longitude);
                    this.$set(this.marker, 'lat', position.coords.latitude);
                    this.$set(this.marker, 'lng', position.coords.longitude);
                    this.enabled = true;
                },
                (error) => {
                    console.log(error);
                    this.geoError = true;
                },
                {
                    timeout: 10000
                });
        },

        methods: {
            search() {
                const {lat, lng} = this.marker;

                axios.get('/events/search', {
                    params: {
                        lat,
                        lng,
                        date:  moment().format()
                    }
                })
                    .then((response) => {
                        console.log(response);
                    });
            }
        }
    }
</script>
