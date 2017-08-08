<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="form-group">
                    <gmap-autocomplete @place_changed="setPlace" class="form-control">
                    </gmap-autocomplete>
                </div>
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

    const moment = require('moment');

    Vue.use(VueGoogleMaps, {
        load: {
            key: 'AIzaSyDBBuh9aUBOJuO0yGB0EDaSz5HmD8lnXKY',
            libraries: 'places'
        }
    });

    export default {
        data() {
            var center = {lat: 33.4483771, lng: -112.07403729999999};
            var marker = {lat: 33.4483771, lng: -112.07403729999999};

            return {
                center,
                marker,
                geoError: false
            };
        },

        mounted() {
            if (!navigator.geolocation)
                return;

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
            },

            setPlace(place) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                this.$set(this.center, 'lat', lat);
                this.$set(this.center, 'lng', lng);
                this.$set(this.marker, 'lat', lat);
                this.$set(this.marker, 'lng', lng);
            }
        }
    }
</script>
