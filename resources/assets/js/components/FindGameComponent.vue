<template>
    <div class="container">
        <div class="row">
            <div class="col-sm-4 col-centered">
                <h4 class="light-text text-center">Select the number of players</h4>
                    <ul class="list-group">
                        <li class="list-group-item" @click="findGame(2)"><h4>2 Players</h4></li>
                        <li class="list-group-item" @click="findGame(4)"><h4>4 Players</h4></li>
                        <li class="list-group-item" @click="findGame(6)"><h4>6 Players</h4></li>
                        <li class="list-group-item" @click="findGame(8)"><h4>8 Players</h4></li>
                        <li class="list-group-item" @click="findGame(0)"><h4>Any Number</h4></li>
                    </ul>

                    <div class="windows8" v-if='waitingCircle'>
                        <div class="wBall" id="wBall_1">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_2">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_3">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_4">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_5">
                            <div class="wInnerBall"></div>
                        </div>
                        <div class="wBall" id="wBall_6">
                            <div class="wInnerBall"></div>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</template>

<script>
    import Swal from 'sweetalert2/dist/sweetalert2.js'
    import 'sweetalert2/src/sweetalert2.scss'
    
    export default {
        data(){
            return  {
                waitingCircle: false,
            }
        },
        mounted() {
        },
        methods: {
            findGame(num){
                console.log('start search')
                this.waitingCircle = true;
                axios.post('/search', {num: num})
                .then((response)=> {
                this.waitingCircle = false;
                console.log(response.data)
                // if (response.data.message == 'not' && !this.waitingCircle){
                //     this.waitingCircle = false;
                //     Swal.fire({
                //       title: 'Not found!',
                //       text: 'Not enough online players! Try another type of game or wait some time',
                //       type: 'info',
                //       confirmButtonText: 'Ok'
                //     })
                // }
                // else if (response.data.message == 'ok'){
                //     location.replace(window.location.origin + '/game');
                // }
              });
            }      
        },
    }
</script>
