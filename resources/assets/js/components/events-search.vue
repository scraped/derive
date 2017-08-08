<template>
    <div class="container">
        <div class="row" v-if="showEvent && !!event">
            <div class="col-md-8 col-md-offset-2">
                <ul class="event-list">
                    <li>
                        <time datetime="2014-07-20">
                            <span class="day">{{ event.startTime.date() }}</span>
                            <span class="month">{{ event.startTime.format('MMM') }}</span>
                            <span class="year">{{ event.startTime.year() }}</span>
                            <span class="time">ALL DAY</span>
                        </time>

                        <div class="info">
                            <h2 class="title">{{ event.name }}</h2>
                            <span>{{ event.startTime }}</span>
                        </div>
                    </li>
                    <li class="event-details">
                        <div class="info" style="height:auto">
                            <p class="desc">
                                {{ event.description }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row" v-if="!showEvent">
            <div class="col-md-8 col-md-offset-2">
                <div class="form-group">
                    <gmap-autocomplete @place_changed="setPlace" :selectFirstOnEnter="true" class="form-control">
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
                        <input type="button" class="btn btn-lg btn-primary" value="Roll" v-on:click="search">
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
                event: {},
                showEvent: false
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
                        this.showEvent = true;

                        const event = response.data;

                        this.event = response.data;
                        this.$set(this.event, 'startTime', moment(event.startTime));
                        if (event.endTime)
                            this.$set(this.event, 'endTime', moment(event.endTime));
                        //{"id":"106688233357249","type":"public","name":"Acoustic Happy Hour with Lindsey Vogt","description":"Live Music Presented by AZ Chicks with Picks.\n\n$4 Local Huss Brewery Pints\n\n$4 House Wines\n\n$4 Cocktail of the Day\n\n1\/2 Priced Appetizers (excluding Jumbo Combo)","startTime":"2017-08-03T16:00:00-0700","endTime":"2017-08-13T19:00:00-0700","place":null,"requested_date":"2017-08-08T01:08:19-04:00"}
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
