<template>
    <div class="row">
        <div class="col-sm-4 col-centered">
           <h3>Bank: {{this.bank}}</h3>
            <div v-if="community" class="community-cards" style="backgroung-color: grey;">
                <p>Community cards</p>
                <img v-if="community.first_card" class="mini-card" :src="community.first_card" />
                <img v-if="community.second_card" class="mini-card" :src="community.second_card" />
                <img v-if="community.third_card" class="mini-card" :src="community.third_card" />
                <img v-if="community.fourth_card" class="mini-card" :src="community.fourth_card" />
                <img v-if="community.fifth_card" class="mini-card" :src="community.fifth_card" />
            </div>
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
        props: ['results', 'bank', 'community'],
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
