<template>
    <div class="container-fluid">
        <div class="row">
              <div class="col-md-12">
                <ul class="list-group">
                  <li class="list-group-item" v-for="player in players">
                    <p>{{player.name}}</p>
                    <p v-if="player.me"><img v-for="card in player.hand" class="mini-card" :src="card.url" /></p>
                    <p v-else><img v-for="card in player.hand" class="mini-card" src="/cards/back.jpg" /></p>
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
          }
        },
        mounted() {
          this.loadGame()
        },
        methods: {
          loadGame: function(){
            axios.get('/api/loadgame').then((response)=> {
              this.players = response.data.players
              if (response.data.game == 'blind-bets'){
                this.prepareDeck()
                this.dealPreflop()
              }
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
