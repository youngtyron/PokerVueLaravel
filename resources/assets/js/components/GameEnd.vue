<template>
    <div class="row">
        <div class="col-sm-4 col-centered">
        	<h3 class="text-center">{{player.name}} {{player.last_name}}</h3>
            <div>
            	<p class="text-center">
	            	<img class="prise-card" src="/cards/spades-1.png" />
	            	<img class="prise-card" src="/cards/diamonds-13.png" />
	            	<img class="prise-card" src="/cards/spades-12.png" />
	            	<img class="prise-card" src="/cards/hearts-11.png" />
	            	<img class="prise-card" src="/cards/clubs-1.png" />
            	</p>

            </div>
            <div>
            	<p class="text-center">
            		<i class="fas fa-star fa-4x" v-for="item in [1,2,3,4,5]" style="color: #F4EE1F; margin-top:10px; margin-left: 7px; 	margin-right: 25px;"></i>
            	</p>
            </div>
        </div>
    </div>
</template>

<script>
    
    export default {
        props: ['player'],
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
