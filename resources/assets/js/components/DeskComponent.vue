<template>
    <div class="container-fluid">
        <div class="row">
              <div class="col">
                <span v-if="game.bank">Bank: {{game.bank}}</span>
                <span v-else>Bank is empty</span>
                <ul class="list-group">
                  <li class="list-group-item" v-for="opponent in opponents" :id='opponent.id'>
                    <h4 class="player-box-text">{{opponent.name}} {{opponent.last_name}}</h4>
                    <img v-if="opponent.first_card" class="my-mini-card" src="cards/back.jpg" />
                    <img v-if="opponent.second_card" class="my-mini-card" src="cards/back.jpg" />
                  </li>
                </ul>
 
<!--                 <button type="button" class="btn btn-primary" @click="startGame">Start game</button>
 -->           </div>
        </div>
        <div class="row">
              <div v-if="community" class="community-cards" style="backgroung-color: grey;">
                <p>Community cards</p>
                <img v-if="community.first_card" class="mini-card" :src="community.first_card" />
                <img v-if="community.second_card" class="mini-card" :src="community.second_card" />
                <img v-if="community.third_card" class="mini-card" :src="community.third_card" />
                <img v-if="community.fourth_card" class="mini-card" :src="community.fourth_card" />
                <img v-if="community.fifth_card" class="mini-card" :src="community.fifth_card" />
              </div>

             <div class="player-box" v-model='player'>
              <h4 class="player-box-text">{{player.name}} {{player.last_name}}</h4>
              <img v-if="player.first_card" class="my-mini-card" :src="player.first_card" />
              <img v-if="player.second_card" class="my-mini-card" :src="player.second_card" />
              <input class="chip" type="image" src="/chips/1.png"  @click="addToken(1)"/>
              <input class="chip" type="image" src="/chips/5.png" @click="addToken(5)"/>
              <input class="chip" type="image" src="/chips/25.png" @click="addToken(25)"/>
              <input class="chip" type="image" src="/chips/100.png" @click="addToken(100)"/>
              <button type="button" class="btn btn-primary" @click="makeBet(bets)">{{bets}}</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['match', 'gamer'],
        data(){
          return {
            player: '',
            game: [],
            community: [],
            bets: 0,
            winner: [],
            opponents: []
          }
        },
        computed: {
          channel(){
            return window.Echo.private('desk-common.' + this.match + '-'+ this.gamer);
          }
        },
        mounted() {
          this.loadGame();
          this.channel
            .listen('DeskCommonEvent', ({data})=>{
              if (data.other == "blinds_done"){
                alert("Blinds is done!")
              }
              console.log(data)
              this.game = data.game
              this.player = data.player
              this.opponents = data.opponents
              this.community = data.community
              if (this.gamer == data.turn){
                if (data.bet_type=='raise'){
                  alert(data.previous.name + ' raises to ' + data.previous.bet + '!')
                }
                else if(data.bet_type=='bet'){
                  alert(data.previous.name + ' bets ' + data.previous.bet + '!')
                }
                else if(data.bet_type=='call'){
                  alert(data.previous.name + ' calls with ' + data.previous.bet + '!')
                }
              }
              if (this.game.phase == 'shotdown'){
                this.winner = data.winner
                // alert (this.winner.player + ' wins!')
              }
            });
        },
        methods: {
          startGame(){
            // console.log('start')
            axios.post('/blinds', {match: this.match}).then((response)=> {
              if (response.data.other == "blinds_done"){
                alert("Blinds is done!")
              }
              if (response.data.other == "blinds_done"){
                alert("Blinds is done!")
              }
              this.game = response.data.game
              this.players = response.data.players
            });
          },
          loadGame: function(){
            axios.get('/loadgame').then((response)=> {
              // console.log(response.data)
              this.player = response.data.player
              this.opponents = response.data.opponents
              this.game = response.data.game
              this.community = response.data.community
              if (this.gamer == response.data.turn){
                alert("Your turn!")
              }
              if (this.game.phase == 'shotdown'){
                this.winner = response.data.winner
                // alert (this.winner.player + ' wins!')
              }
            })
            .catch((error)=>{
              console.log(error);
            });
          },
          addToken(token){
            this.bets = this.bets + token;
          },
          makeBet: function(bet){
            if (bet + this.player.last_bet < this.game.max_bet){
              alert('Your bet is too small')
            }
            else{
              axios.post('/bet', {bet: bet, match: this.match})
                .then((response)=> {
                console.log(response.data)      
                this.player = response.data.player
                this.opponents = response.data.opponents
                this.game = response.data.game
                this.community = response.data.community
              });
            }
          },
          dealPreflop(){
            axios.get('/dealpreflop').then((response)=> {
              this.players = response.data
            });
          },
          cleatBets(){
            this.bets = 0;
          },
          passRound(){
            console.log("I'm passing")
              axios.post('/pass', {match: this.match})
                .then((response)=> {
                // console.log(response.data)      
                this.game = response.data.game
                this.community = response.data.community
                this.player = response.data.player
                this.opponents = response.data.opponents
              });
          },
        }
    }
</script>
