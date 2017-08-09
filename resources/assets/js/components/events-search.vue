<template>
    <div>
        <div class="row" v-if="showEvent && !!event && !searching">
            <div class="col-md-8 col-md-offset-2">
                <div class="clearfix">
                    <a v-on:click="showMap()" class="pull-left">
                        <span class="glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon-map-marker"></span>
                    </a>
                </div>
                <div class="event card">
                    <input type="hidden" id="event-id" v-bind:value="event.id">
                    <h1 class="card-title">
                        <a v-bind:href="eventUrl" target="_blank">
                            <img class="card-img-top" v-bind:src="event.picture.data.url" v-if="event.picture && event.picture.data">
                        </a>
                    </h1>
                    <div class="card-block">
                        <h1 class="card-title">
                            <a v-bind:href="eventUrl" target="_blank">
                                {{ event.name }}
                            </a>
                        </h1>
                        <h2 class="card-text">
                            <span v-if="event.startTime">{{ event.startTime.calendar() }}</span>
                            <span v-if="event.endTime"> until {{ event.endTime.calendar() }}</span>
                        </h2>
                        <div class="card-text address" v-if="event.place">
                            <h3>{{ event.place.name }}</h3>
                            {{ event.place.location.street }}
                            <br>
                            {{ event.place.location.city }}, {{ event.place.location.state }} {{ event.place.location.zip }}
                        </div>
                        <p class="card-text" v-html="event.description"></p>
                    </div>
                </div>
                <div class="form-inline">
                    <div class="form-group">
                        <input type="button" class="btn btn-lg btn-primary" value="Reroll" v-on:click="search">
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="!showEvent && !searching">
            <div class="col-md-8 col-md-offset-2">
                <div class="form-group">
                    <gmap-autocomplete @place_changed="setPlace" :selectFirstOnEnter="true" class="form-control autocomplete">
                    </gmap-autocomplete>
                </div>
                <gmap-map
                    :center="center"
                    :zoom="10"
                    map-type-id="terrain"
                    class="img-responsive">
                    <gmap-marker
                        :position.sync="marker"
                        :draggable="true"
                        @position_changed="updateMarker">
                    </gmap-marker>
                </gmap-map>
            </div>
            <div class="col-md-8 col-md-offset-2" style="padding-top:20px">
                <div class="form-inline">
                    <div class="form-group">
                        <input type="button" class="btn btn-lg btn-primary" value="Roll" v-on:click="search" id="roll-button">
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="searching">
            <div class="col-md-8 col-md-offset-2">
                <div id="floatingCirclesG">
                    <div class="f_circleG" id="frotateG_01"></div>
                    <div class="f_circleG" id="frotateG_02"></div>
                    <div class="f_circleG" id="frotateG_03"></div>
                    <div class="f_circleG" id="frotateG_04"></div>
                    <div class="f_circleG" id="frotateG_05"></div>
                    <div class="f_circleG" id="frotateG_06"></div>
                    <div class="f_circleG" id="frotateG_07"></div>
                    <div class="f_circleG" id="frotateG_08"></div>
                </div>
            </div>
        </div>
        <transition name="slide-fade">
            <div class="popupunder alert alert-danger" v-if="error">
                <button type="button" class="close close-sm" data-dismiss="alert">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
                <strong>Error: </strong> {{ error }}
            </div>
        </transition>
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
                error: null,
                event: {},
                searching: false,
                showEvent: false
            };
        },

        computed: {
            eventUrl() {
                return `https://www.facebook.com/events/${this.event.id}`;
            }
        },

        mounted() {
            $('input.autocomplete').focus();

            if (!navigator.geolocation)
                return;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.$set(this.center, 'lat', position.coords.latitude);
                    this.$set(this.center, 'lng', position.coords.longitude);
                    this.$set(this.marker, 'lat', position.coords.latitude);
                    this.$set(this.marker, 'lng', position.coords.longitude);
                    this.enabled = true;
                });
        },

        methods: {
            search() {
                const {lat, lng} = this.marker;

                this.searching = true;

                var params = {
                    lat,
                    lng,
                    date: moment().format()
                };

                if (window.fbToken) {
                    params.fbToken = window.fbToken;
                }

                axios.get('/events/search', {params})
                    .then((response) => {
                        this.showEvent = true;

                        const event = response.data;
                        this.event = event;

                        this.searching = false;

                        this.$set(this.event, 'startTime', moment(event.startTime));
                        if (event.endTime)
                            this.$set(this.event, 'endTime', moment(event.endTime));
                    })
                    .catch((error) => {
                        this.error = 'No events found for today!';
                        this.searching = false;
                        this.showEvent = false;

                        window.setTimeout(function() {
                            $('input.autocomplete').focus();
                        }, 500);

                        window.setTimeout(() => {
                            this.error = null;
                        }, 5000);
                    });
            },

            setPlace(place) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                this.$set(this.center, 'lat', lat);
                this.$set(this.center, 'lng', lng);
                this.$set(this.marker, 'lat', lat);
                this.$set(this.marker, 'lng', lng);
                $('#roll-button').focus();
            },

            showMap() {
                this.showEvent = false;
            },

            updateMarker(e) {
                this.$set(this.marker, 'lat', e.lat());
                this.$set(this.marker, 'lng', e.lng());
            }
        }
    }
</script>
