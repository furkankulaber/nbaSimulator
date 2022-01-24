<template>
    <div class="container py-3">
        <div class="d-flex justify-content-center">
            <button class="btn btn-warning" v-if="this.totalMatch < 1" @click="createMatch">Create Match First Week</button>
            <button class="btn btn-success mb-2" v-if="this.totalMatch > 0 && this.runningStatus === false" @click="simulateGame">Simulate Week</button>
        </div>
        <div class="row" v-for="(week,index) in this.matchs">
            <div class="col" style="margin-bottom: 50px;">
                <h2 style="text-align: center">{{ index }}. Week</h2>
                <div class="row matches" v-for="(match,mIndex) in week">
                    <div class="col">
                        <div class="col-12 bborder-bottom">
                            <div class="d-flex justify-content-start">
                                <div class="col">
                                    <img :src="'assets/images/team/'+match.home.abbreviation+'.svg'"
                                         :alt="match.home.full_name" style="max-width: 100px;">
                                </div>
                                <div class="col d-flex align-items-center">
                                    <h4>{{ match.home.full_name }}</h4>
                                </div>
                                <div class="col d-flex align-items-center justify-content-end">
                                    <h3>{{ match.home_score }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-column align-items-center">
                                <div class="p-2 bborder-bottom">Attack: {{ match.position.filter((position) => position.attacker.id == match.home.id).length }}</div>
                                <div class="p-2 bborder-bottom">
                                    <p v-for="pos in match.position.filter((position) => position.attacker.id == match.home.id)">Attack : {{ getName(pos.attacking_player) }} | Assist: {{ getName(pos.assist_player) }} | Score: {{pos.score}}</p>
                                </div>
                                <div class="p-2">
                                    <h5 style="width: 100%;text-align: center;font-weight: bold">Player</h5>
                                    <p v-for="posto in match.pos.player" v-if="posto.team == match.home.id">{{posto.name}} | Attack : {{ checkerD(posto.attack) }} | Assist: {{ checkerD(posto.assist) }} | Three : {{ checkerD(posto.b_3) }} | Two : {{ checkerD(posto.b_2) }} | Missed : {{ checkerD(posto.b_0) }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-1 d-flex align-items-start">
                        <h5 style="width: 100%;text-align: center" class="mt-4">{{match.minutes}}:00</h5>
                    </div>
                    <div class="col">
                        <div class="col-12 bborder-bottom">
                            <div class="d-flex justify-content-end">
                                <div class="col d-flex align-items-center justify-content-start">
                                    <h3>{{ match.away_score }}</h3>
                                </div>
                                <div class="col d-flex align-items-center">
                                    <h4>{{ match.away.full_name }}</h4>
                                </div>
                                <div class="col-4">
                                    <img :src="'assets/images/team/'+match.away.abbreviation+'.svg'"
                                         :alt="match.away.full_name" style="max-width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-column align-items-center">
                                <div class="p-2 bborder-bottom">Attack: {{ match.position.filter((position) => position.attacker.id == match.away.id).length }}</div>
                                <div class="p-2 bborder-bottom">
                                    <p v-for="pos in match.position.filter((position) => position.attacker.id == match.away.id)">Attack : {{ getName(pos.attacking_player) }} | Assist: {{ getName(pos.assist_player) }} | Score: {{pos.score}}</p>
                                </div>
                                <div class="p-2">
                                    <h5 style="width: 100%;text-align: center;font-weight: bold">Player</h5>
                                    <p v-for="posto in match.pos.player" v-if="posto.team == match.away.id">{{posto.name}} | Attack : {{ checkerD(posto.attack) }} | Assist: {{ checkerD(posto.assist) }} | Three : {{ checkerD(posto.b_3) }} | Two : {{ checkerD(posto.b_2) }} | Missed : {{ checkerD(posto.b_0) }} </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            'matchs': [],
            'runningStatus': false,
            'totalMatch': 0,
        }
    },
    created() {
        this.getMatches();
    },
    methods: {
        createMatch() {
            this.axios
                .get('http://localhost:8080/api/createMatch')
                .then(response => {
                    if(response.data.status == true)
                    {
                        this.getMatches();
                    }
                });
        },
        timeout(ms) { //pass a time in milliseconds to this function
            return new Promise(resolve => setTimeout(resolve, ms));
        },
        simulateReq()
        {
            this.axios
                .get('http://localhost:8080/api/simulateGame')
                .then(response => {
                    this.matchs = response.data;
                    this.totalMatch = Object.keys(this.matchs).length;
                });
        },
        simulateGame()
        {
            this.runningStatus = true;
            window.setInterval(() => {
                this.simulateReq()
            },5000);
        },
        getData() {
            this.axios
                .get('http://localhost:8080/api/getData')
                .then(response => {
                    this.matchs = response.data;
                    this.totalMatch = Object.keys(this.matchs).length;
                });
        },
        getMatches()
        {
            this.axios
                .get('http://localhost:8080/api/getMatches')
                .then(response => {
                    this.matchs = response.data;
                    this.totalMatch = Object.keys(this.matchs).length;
                });
        },
        getName(data)
        {
            return data.first_name+' '+data.last_name;
        },
        checkerD(data)
        {
            return data ?? 0;
        }
    },
    mounted() {
        console.log('Component mounted.')
    }
}
</script>
<style>

.bborder-top{
    border-top: 1px solid #f3f3f3;
}
.bborder-bottom{
    border-bottom: 1px solid #f3f3f3;
}

.matches {
    background-color: #ffffff;
    margin: 0px 10px;
    border-bottom: 1px solid #f3f3f3;
}

.matches:nth-child(odd) {
    background-color: #fcfcfc;
    padding: 5px 10px;
}

.matches .col-1 {
    border-left: 1px solid #f3f3f3;
    border-right: 1px solid #f3f3f3;
}


</style>
