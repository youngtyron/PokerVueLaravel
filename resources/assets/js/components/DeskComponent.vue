<template>
    <div class="container-fluid">
        <div class="row">
              <div class="col-md-12">

                <h1 @click="changeTest">{{test}}</h1>

                <span v-if="game.bank">Bank: {{game.bank}}</span>
                <span v-else>Bank is empty</span>
                <ul class="list-group">
                  <li class="list-group-item" v-for="player in players">
                    <div class="player" v-if="player.me">
                      <p>{{player.name}}</p>
                      <p>Money:{{player.money}}</p>
                      <p><img v-for="card in player.hand" class="mini-card" :src="card.url" /></p>
                      <button type="button" class="btn btn-danger" @click="makeBet">Bet</button>
                    </div>
                    <div class="player" v-else>
                      <p>{{player.name}}</p>
                      <p>Money:{{player.money}}</p>
                      <p><img v-for="card in player.hand" class="mini-card" src="/cards/back.jpg" /></p>
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
            test: 'hello',


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
          changeTest(){
              this.test = 'bye bye'
          },


          loadGame: function(){
            axios.get('/api/loadgame').then((response)=> {
              this.players = response.data.players
              this.game = response.data.game
              this.bank = response.data.game.bank
              if (this.game.phase == 'blind-bets'){
                console.log('blind-bets')
              }
              else if (this.game.phase == 'preflop'){
                this.prepareDeck()
                this.dealPreflop()
              }
            });
          },
          makeBet: function(){
            axios.post('/bet', {bet: 100})
              .then((response)=> {
              this.game = response.data.game
            });
          },
          randCard(){
            var rand = Math.floor(Math.random() * this.deck.length);
            var card = this.deck[rand];
            this.deck.splice(this.deck.indexOf(card), 1);
            return card;
          },
          prepareDeck(){
            var ranks = [1,2,3,4,5,6,7,8,9,10,11,12,13];
            for (var i=0; i<ranks.length; i++){
              this.deck.push({suit: 'spades', rank: ranks[i], url: '/cards/spades-' + String(ranks[i]) + '.png'});
              this.deck.push({suit: 'hearts', rank: ranks[i], url: '/cards/hearts-' + String(ranks[i]) + '.png'});
              this.deck.push({suit: 'diamonds', rank: ranks[i], url: '/cards/diamonds-' + String(ranks[i]) + '.png'});
              this.deck.push({suit: 'clubs', rank: ranks[i], url: '/cards/clubs-' + String(ranks[i]) + '.png'});
            }
          },
          dealPreflop(){
            for (var i=0; i<this.players.length; i++){
              this.players[i].hand.push(this.randCard())
              this.players[i].hand.push(this.randCard())
            }
          },
        }
    }
</script>
