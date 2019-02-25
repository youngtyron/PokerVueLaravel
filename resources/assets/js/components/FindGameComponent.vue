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
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return  {
            }
        },
        mounted() {
        },
        methods: {
            findGame(num){
                console.log('start search')
                axios.post('/search', {num: num})
                .then((response)=> {
                console.log(response.data)      
              });
            }      
        },
    }
</script>
