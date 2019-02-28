<template>
    <div class="row">
        <div class="col-sm-4 col-centered">
           <ul class="list-group">
               <li class="list-group-item" v-for="result in results">
                  <p>{{result.name}} {{result.last_name}}</p>
                   <p>{{result.combination}}</p>
                    <img class="my-mini-card" :src="result.first_card" />
                    <img class="my-mini-card" :src="result.second_card" />
                   <i class="fas fa-star fa-2x" v-if='result.winner' style="color: #F4EE1F;"></i>
               </li>
           </ul>
        </div>
    </div>
</template>

<script>
    
    export default {
        props: ['results'],
        data(){
            return  {
            }
        },
        mounted() {
        },
        methods: {
        
        },
    }
</script>
