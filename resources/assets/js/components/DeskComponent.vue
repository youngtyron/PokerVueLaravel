<template>
    <div class="container-fluid">
        <RoundResultsSlot v-if="roundend" :results='this.results' :bank="this.bank" :community="this.community_cards"></RoundResultsSlot>
        <button v-if="roundend" type="button" class="btn btn-info" @click = 'nextRound'>Next Round</button>
        <div class="row" id="bank-row">
          <div class="col-md-4 col-centered">
              <span class="text-center" v-if="game.bank">Bank: {{game.bank}}</span>
              <span class="text-center" v-else>Bank is empty</span>
          </div>
        </div>
        <div class="row" id='game-row'>
              <div class="col-md-3">
                <div class="player-box" v-model='player'>
                  <h4 class="player-box-text">{{player.name}} {{player.last_name}}</h4>
                  <h4 class="player-box-text">Money: {{player.money}}</h4>
                  <p>
                    <img v-if="player.first_card" class="my-mini-card" :src="player.first_card" />
                    <img v-if="player.second_card" class="my-mini-card" :src="player.second_card" />
                  </p>
                  <div v-if='next'>
                    <p>
                      <button type="button" class="chip-button" @click="addToken(5)">5</button>
                      <button type="button" class="chip-button" @click="addToken(10)">10</button>
                      <button type="button" class="chip-button" @click="addToken(25)">25</button>
                      <button type="button" class="chip-button" @click="addToken(50)">50</button>
                      <button type="button" class="chip-button" @click="addToken(5000)">100</button>
                    </p>
                    <p class="player-box-text">Current bet: {{bets}}</p>
                    <button type="button" class="bet-button" @click="makeBet(bets)">Bet</button>
                    <button type="button" class="clear-button" @click="clearBets">Clear</button>
                  </div>
                  <div v-else>
                    <p>DISABLED</p>
                  </div>
                </div>
              </div>
               <div class="col-md-6">
                  <div class="list-group-item" v-for="opponent in opponents" :id='opponent.id'>
                    <h4 class="player-box-text">{{opponent.name}} {{opponent.last_name}}</h4>
                    <img v-if="opponent.first_card" class="my-mini-card" src="cards/back.jpg" />
                    <img v-if="opponent.second_card" class="my-mini-card" src="cards/back.jpg" />
                  </div>
               </div>

               <div class="col-md-3">
                  <div v-if="community" class="community-cards" style="backgroung-color: grey;">
                    <p>Community cards</p>
                    <img v-if="community.first_card" class="mini-card" :src="community.first_card" />
                    <img v-if="community.second_card" class="mini-card" :src="community.second_card" />
                    <img v-if="community.third_card" class="mini-card" :src="community.third_card" />
                    <img v-if="community.fourth_card" class="mini-card" :src="community.fourth_card" />
                    <img v-if="community.fifth_card" class="mini-card" :src="community.fifth_card" />
                  </div>  
               </div>
        </div>
    </div>
</template>

<script>
    import RoundResultsSlot from './RoundResults.vue'
    export default {
        props: ['match', 'gamer'],
        components: {
          RoundResultsSlot,
        },
        data(){
          return {
            player: '',
            game: [],
            community: [],
            bets: 0,
            winner: [],
            opponents: [],
            next: false,
            results: [],
            bank: 0,
            community_cards: [],
            roundend: false
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
              if (data.end){
                this.roundend = true
                this.results = data.results.results 
                this.bank = data.results.bank 
                this.community_cards = data.results.community 
                document.getElementById('game-row').style.display = 'none';
                document.getElementById('bank-row').style.display = 'none';
              }
              else{
                this.game = data.game
                this.player = data.player
                this.opponents = data.opponents
                this.community = data.community
                alert(data.message)
                if(data.next){
                  this.next = true;
                  if (data.minimum){
                    alert('Your turn! Mininmal bet is '+ data.minimum)
                  }
                  else{
                    alert('Your turn!')
                  }
                }
                if (data.loosers){
                  console.log('loosers!')
                }

                // if (data.other == "blinds_done"){
                //   alert("Blinds is done!")
                // }
                // this.game = data.game
                // this.player = data.player
                // this.opponents = data.opponents
                // this.call = data.call
                // this.community = data.community
                // if (this.gamer == data.turn){
                //   if (data.bet_type=='raise'){
                //     alert(data.previous.name + ' raises to ' + data.previous.bet + '!')
                //   }
                //   else if(data.bet_type=='bet'){
                //     alert(data.previous.name + ' bets ' + data.previous.bet + '!')
                //   }
                //   else if(data.bet_type=='call'){
                //     alert(data.previous.name + ' calls with ' + data.previous.bet + '!')
                //   }
                // }
              }

            });
        },
        methods: {
          startGame(){
            axios.post('/blinds', {match: this.match}).then((response)=> {
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
              if (response.data.end){
                this.roundend = true
                this.results = response.data.results.results 
                this.bank = response.data.results.bank 
                this.community_cards = response.data.results.community 
                document.getElementById('game-row').style.display = 'none';
                document.getElementById('bank-row').style.display = 'none';
              }
              else{
                this.player = response.data.player
                this.opponents = response.data.opponents
                this.game = response.data.game
                this.call = response.data.call
                if (this.gamer == response.data.turn){
                  this.next = true;
                }
                this.community = response.data.community
                if (this.gamer == response.data.turn){
                  alert("Your turn!")
                }
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
            console.log('start bet')
            if (bet + this.player.last_bet < this.game.max_bet){
              alert('Your bet is too small')
            }
            else{
              axios.post('/bet', {bet: bet, match: this.match})
                .then((response)=> {
                this.bets = 0;
                console.log('end bet')
                console.log(response.data)   
                if (response.data.end){
                  this.roundend = true
                  this.results = response.data.results.results 
                  this.bank = response.data.results.bank 
                  this.community_cards = response.data.results.community 
                  document.getElementById('game-row').style.display = 'none';
                  document.getElementById('bank-row').style.display = 'none';
                }   
                else{
                  // this.player = response.data.player
                  // this.opponents = response.data.opponents
                  // this.call = response.data.call
                  // this.game = response.data.game
                  // this.community = response.data.community
                    this.game = response.data.game
                    this.player = response.data.player
                    this.opponents = response.data.opponents
                    this.community = response.data.community
                    if(response.data.next){
                      alert('Your turn!')
                      this.next = true;
                    }
                    else{
                      this.next = false;
                    }
                    if (response.data.loosers){
                      console.log('loosers!')
                    }
                }
              });
            }
          },
          dealPreflop(){
            axios.get('/dealpreflop').then((response)=> {
              this.players = response.data
            });
          },
          clearBets(){
            this.bets = 0;
          },
          passRound(){
            console.log("I'm passing")
              axios.post('/pass', {match: this.match})
                .then((response)=> {
                // console.log(response.data)      
                this.game = response.data.game
                this.community = response.data.community
                this.call = response.data.call
                this.player = response.data.player
                this.opponents = response.data.opponents
              });
          },
          nextRound(){
            axios.post('/nextround').then((response)=> {
              console.log(response.data)
              this.loadGame();
            });
          }
        }
    }
</script>
