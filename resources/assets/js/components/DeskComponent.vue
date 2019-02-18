<template>
    <div class="container-fluid">
        <div class="row">
              <div class="col-md-6">
                <span v-if="game.bank">Bank: {{game.bank}}</span>
                <span v-else>Bank is empty</span>
                <ul class="list-group">
                  <li class="list-group-item" v-for="player in players" :id='player.id'>
                    <div class="player" v-if="player.id == gamer">
                      <p v-if="player.button">BUTTON</p>
                      <p v-if="player.small_blind">SMALL BLIND</p>
                      <p v-if="player.big_blind">BIG BLIND</p>
                      <p v-if='player.passing!=0'>PLAYER IS OUT OF GAME</p>
                      <p>{{player.name}}</p>
                      <p>Money:{{player.money}}</p>
                      <p><img v-if="player.first_card" class="mini-card" :src="player.first_card" />
                         <img v-if="player.second_card" class="mini-card" :src="player.second_card" /></p>
                          <div class="tokens-buttons" v-if="player.current">
                            <p>[CURRENT]</p>
                            <button type="button" class="btn btn-info tokens-button" @click="addToken(5)">5</button>
                            <button type="button" class="btn btn-info tokens-button" @click="addToken(10)">10</button>
                            <button type="button" class="btn btn-info tokens-button" @click="addToken(25)">25</button>
                            <button type="button" class="btn btn-info tokens-button" @click="addToken(50)">50</button>
                            <button type="button" class="btn btn-info tokens-button" @click="addToken(100)">100</button>
                            <button v-model="bets" type="button" class="btn btn-info bets-button" @click="makeBet(bets)">{{bets}}</button>

                                                            <button type="button" class="pass-button btn btn-info" @click="passRound">Pass</button>

                            
                            <p @click="cleatBets">Clear bet</p>
                          </div>
                          <div class="tokens-buttons" v-else>
                            <p>[NOT MY TURN]</p>
                            <button disabled type="button" class="btn btn-info tokens-button">5</button>
                            <button disabled type="button" class="btn btn-info tokens-button">10</button>
                            <button disabled type="button" class="btn btn-info tokens-button">25</button>
                            <button disabled type="button" class="btn btn-info tokens-button">50</button>
                            <button disabled type="button" class="btn btn-info tokens-button">100</button>
                            <button disabled v-model="bets" type="button" class="btn btn-info bets-button">{{bets}}</button>
                                                      <button disabled type="button" class="pass-button btn btn-info">Pass</button>

                          </div>

                    </div>
                    <div class="player" v-else>
                      <p v-if="player.button">BUTTON</p>
                      <p v-if="player.small_blind">SMALL BLIND</p>
                      <p v-if="player.big_blind">BIG BLIND</p>
                      <p v-if="player.current">YOUR MOVE!</p>

                      <p>{{player.name}}</p>
                      <p>Money:{{player.money}}</p>
                      <p><img v-if="player.first_card" class="mini-card" src="/cards/back.jpg" />
                         <img v-if="player.second_card" class="mini-card" src="/cards/back.jpg" /></p>
                    </div>
                  </li>
                </ul>
                <button type="button" class="btn btn-primary" @click="startGame">Start game</button>
              </div>
              <div v-if="community" class="col-md-6" style="backgroung-color: grey;">
                <p>Community cards</p>
                <img v-if="community.first_card" class="mini-card" :src="community.first_card" />
                <img v-if="community.second_card" class="mini-card" :src="community.second_card" />
                <img v-if="community.third_card" class="mini-card" :src="community.third_card" />
                <img v-if="community.fourth_card" class="mini-card" :src="community.fourth_card" />
                <img v-if="community.fifth_card" class="mini-card" :src="community.fifth_card" />
              </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['match', 'gamer'],
        data(){
          return {
            deck: [],
            players: [],
            game: [],
            community: [],
            bets: 0,
          }
        },
        computed: {
          deskCommonChannel(){
            return window.Echo.private('desk-common.' + this.match + '-'+ this.gamer);
          }
        },
        mounted() {
          this.loadGame();
          this.deskCommonChannel
            .listen('DeskCommonEvent', ({data})=>{
              if (data.other == "blinds_done"){
                alert("Blinds is done!")
              }
              console.log('event!!!')
              this.game = data.game
              this.players = data.players
              this.community = data.community
              console.log(data.message)
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
            });
        },
        methods: {
          startGame(){
            // console.log('start')
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
              this.players = response.data.players
              this.game = response.data.game
              this.community = response.data.community
              if (this.game.phase == 'blind-bets'){
              }
              else if (this.game.phase == 'preflop'){
                // console.log('preflop')
              }
              else if (this.game.phase == 'flop'){
                // console.log('flop')
              }
              if (this.gamer == response.data.turn){
                // alert("Your turn!")
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
            var me = this.findMeInPlayers();
            if (bet + me.last_bet < this.game.max_bet){
              alert('Your bet is too small')
            }
            else{
              // console.log('bet go')
              axios.post('/bet', {bet: bet, match: this.match})
                .then((response)=> {
                console.log(response.data)      
                // this.players = response.data.players
                // this.game = response.data.game
                // this.community = response.data.community
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
          findMeInPlayers(){
            for (var i=0; i<this.players.length; i++){
              if (this.players[i].id == this.gamer){
                var me = this.players[i]
              }
            }
            return me;
          },
          passRound(){
            console.log("I'm passing")
              axios.post('/pass', {match: this.match})
                .then((response)=> {
                // console.log(response.data)      
                // this.players = response.data.players
                // this.game = response.data.game
                // this.community = response.data.community
              });
          }
        }
    }
</script>
