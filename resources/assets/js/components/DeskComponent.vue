<template>
    <div class="container-fluid">
        <RoundResultsSlot v-if="roundend" :results='results' :bank="bank" :community="community_cards"></RoundResultsSlot>
        <GameEndSlot v-if='gameend' :player='game_end_player'></GameEndSlot>

        <button v-if="roundend" type="button" class="btn btn-info" @click = 'nextRound'>Next Round</button>
        <div class="row" v-if="!gameend && !roundend" id="bank-row">
          <div class="col-md-4 col-centered">
              <p class="text-center" v-if="game.bank">Bank: {{game.bank}}</p>
              <p class="text-center" v-else>Bank is empty</p>
              <p class="text-center" v-if="game.bank">Current bet is {{game.max_bet}}</p>
          </div>
        </div>
        <div class="row" id='game-row' v-if="!gameend && !roundend">
              <div class="col-md-3">
                <div class="player-box" v-model='player'>
                  <h4 class="player-box-text">{{player.name}} {{player.last_name}}</h4>
                  <h4 class="player-box-text">Money: {{player.money}}</h4>
                  <h5 v-if="player.last_bet" class="player-box-text">My bet in this act is {{player.last_bet}}</h5>
                  <h5 v-else class="player-box-text">I've not made any bet</h5>
                  <p>
                    <img v-if="player.first_card" class="my-mini-card" :src="player.first_card" />
                    <img v-if="player.second_card" class="my-mini-card" :src="player.second_card" />
                  </p>
                  <div v-if='next'>
                    <p>
                      <i class="fas fa-coins fa-2x" style="color: black;" @click="addToken(5)">5</i>
                      <i class="fas fa-coins fa-2x" style="color: black;" @click="addToken(10)">10</i>
                      <i class="fas fa-coins fa-2x" style="color: black;" @click="addToken(25)">25</i>
                      <i class="fas fa-coins fa-2x" style="color: black;" @click="addToken(50)">50</i>
                      <i class="fas fa-coins fa-2x" style="color: black;" @click="addToken(100)">100</i>
                    </p>
                    <p class="player-box-text">Current bet: {{bets}}</p>
                    <button type="button" class="bet-button" @click="makeBet(bets)">Bet</button>
                    <button type="button" class="clear-button" @click="clearBets">Clear</button>
                    <button type="button" class='btn btn-info' @click="foldRound">Fold</button>
                  </div>
                  <div v-else>
                    <p>
                      <i class="fas fa-coins fa-2x" style="color: black; opacity: 0.3;">5</i>
                      <i class="fas fa-coins fa-2x" style="color: black; opacity: 0.3;">10</i>
                      <i class="fas fa-coins fa-2x" style="color: black; opacity: 0.3;">25</i>
                      <i class="fas fa-coins fa-2x" style="color: black; opacity: 0.3;">50</i>
                      <i class="fas fa-coins fa-2x" style="color: black; opacity: 0.3;">100</i>
                    </p>
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
    import Swal from 'sweetalert2/dist/sweetalert2.js'
    import 'sweetalert2/src/sweetalert2.scss'
    import RoundResultsSlot from './RoundResults.vue'
    import GameEndSlot from './GameEnd.vue'
    export default {
        props: ['match', 'gamer'],
        components: {
          RoundResultsSlot,
          GameEndSlot,
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
            roundend: false,
            gameend: false,
            game_end_player: [],
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
              console.log('data')
              if(data.you_lose){
                Swal.fire({ 
                  title: 'You lose!',
                  text: "You have not enough chips for playing",
                  confirmButtonText: 'Close'
                }).then(result => {
              if (result.value) {
                location.replace(window.location.origin + '/findgame');
              }
            });   
                
              }
              else{
                if (data.game_end){
                  this.gameend = true
                  this.game_end_player = data.player            
                }
                else if (data.end){
                  this.roundend = true
                  this.results = data.results.results 
                  this.bank = data.results.bank 
                  this.community_cards = data.results.community 
                }
                else{
                    if(data.next){
                      this.next = true;
                      if (data.minimum && data.message){
                        Swal.fire({ 
                          title: data.message,
                          text: 'Your turn! Mininmal bet is '+ data.minimum,
                          confirmButtonText: 'Close'
                        })
                      }
                      else if (data.message){
                        Swal.fire({ 
                          title: data.message,
                          text: 'Your turn!',
                          confirmButtonText: 'Close'
                        })
                      }
                      else{
                        Swal.fire({ 
                          title: data.message,
                          text: 'Your turn! Now you can make your bet',
                          confirmButtonText: 'Close'
                        })
                      }
                  }
                  else{
                    Swal.fire({ 
                        title: data.message,
                        confirmButtonText: 'Close'
                    })
                  }
                  this.game = data.game
                  this.player = data.player
                  this.opponents = data.opponents
                  this.community = data.community

                  if (data.loosers){
                    console.log('loosers!')
                  }
                }               
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
              console.log(response.data)
              if (response.data.start){
                Swal.fire({ 
                  title: 'New round!',
                  text: 'Blinds done!',
                  confirmButtonText: 'Ok'
                })
              }
              if (response.data.game_end){
                  this.gameend = true
                  this.game_end_player = response.data.player            
                }
              else if (response.data.end){
                this.roundend = true
                this.results = response.data.results.results 
                this.bank = response.data.results.bank 
                this.community_cards = response.data.results.community 
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
                  Swal.fire({ 
                    title: 'Your turn!',
                    text: 'Now you can make your bet',
                    confirmButtonText: 'Close'
                  })
                }
              }

            })
            .catch((error)=>{
              console.log(error);
            });
          },
          addToken(token){
            if (token+this.bets>this.player.money){
              Swal.fire({ 
                title: 'You have not enough money to bet it!',
                text: 'Choose lesser chip',
                confirmButtonText: 'Close'
              });
            }
            else{
              this.bets = this.bets + token;              
            }
          },
          makeBet: function(bet){
            console.log('start bet')
            if (bet>0){
              this.next = false;
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
          foldRound(){
            console.log("I'm passing")
            Swal.fire({
              text: 'Are you sure you want to fold?',
              showLoaderOnConfirm: true,
              showCancelButton: true,
            }).then(result => {
              if (result.value) {
                axios.post('/fold', {match: this.match})
              .then((response)=> {
                this.game = response.data.game
                this.community = response.data.community
                this.call = response.data.call
                this.player = response.data.player
                this.opponents = response.data.opponents
              });
              }
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
