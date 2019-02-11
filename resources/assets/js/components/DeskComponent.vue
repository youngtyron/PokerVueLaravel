<template>
    <div class="container-fluid">
        <div class="row">
              <div class="col-md-12">
                <span v-if="game.bank">Bank: {{game.bank}}</span>
                <span v-else>Bank is empty</span>
                <ul class="list-group">
                  <li class="list-group-item" v-for="player in players" :id='player.id'>
                    <div class="player" v-if="player.me">
                      <p v-if="player.button">BUTTON</p>
                      <p v-if="player.small_blind">SMALL BLIND</p>
                      <p v-if="player.big_blind">BIG BLIND</p>
                      <p>{{player.name}}</p>
                      <p>Money:{{player.money}}</p>
                      <p><img v-if="player.first_card" class="mini-card" :src="player.first_card" />
                         <img v-if="player.second_card" class="mini-card" :src="player.second_card" /></p>
                         <button type="button" class="btn btn-outline-dark" @click="makeBet(5)">5</button>
                         <button type="button" class="btn btn-outline-dark" @click="makeBet(10)">10</button>
                         <button type="button" class="btn btn-outline-dark" @click="makeBet(25)">25</button>
                         <button type="button" class="btn btn-outline-dark" @click="makeBet(50)">50</button>
                         <button type="button" class="btn btn-outline-dark" @click="makeBet(100)">100</button>
                    </div>
                    <div class="player" v-else>
                      <p v-if="player.button">BUTTON</p>
                      <p v-if="player.small_blind">SMALL BLIND</p>
                      <p v-if="player.big_blind">BIG BLIND</p>
                      <p>{{player.name}}</p>
                      <p>Money:{{player.money}}</p>
                      <p><img v-if="player.first_card" class="mini-card" src="/cards/back.jpg" />
                         <img v-if="player.second_card" class="mini-card" src="/cards/back.jpg" /></p>
                    </div>
                  </li>
                </ul>
                <p></p>
              </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
          return {
            deck: [],
            players: [],
            game: [],
            bank: '',
          }
        },
        computed: {
          channel(){
            return window.Echo.private('game.' + this.game.id);
          }
        },
        mounted() {
          this.loadGame()
          // this.channel
          //   .listen('Game', ({data})=>{
          //     console.log('listening game')
          //   })
          // })
        },
        methods: {
          loadGame: function(){
            axios.get('/api/loadgame').then((response)=> {
              this.players = response.data.players
              this.game = response.data.game
              this.bank = response.data.game.bank
              if (this.game.phase == 'blind-bets'){

              }
              else if (this.game.phase == 'preflop'){
                console.log('preflop')
                this.dealPreflop()
              }
            });
          },
          makeBet: function(tokens){
            axios.post('/bet', {bet: tokens})
              .then((response)=> {
              this.game = response.data.game
            });
          },
          dealPreflop(){
            axios.get('/api/dealpreflop').then((response)=> {
              this.players = response.data
            });
          },
        }
    }
</script>
