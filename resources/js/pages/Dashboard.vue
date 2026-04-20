<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const dadosPessoais = {
    nome: 'Luiz Eduardo',
    cargo: 'Desenvolvedor PHP Fullstack',
    local: 'Minas Gerais - MG',
    whatsapp: '35 92000-7412',
    linkWhats: 'https://wa.me/5535920007412'
};

const techIcons = [
{ src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linux/linux-original.svg", alt: "linux" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/ubuntu/ubuntu-plain.svg", alt: "ubuntu" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg", alt: "apache" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nginx/nginx-original.svg", alt: "nginx" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg", alt: "php" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg", alt: "laravel" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/composer/composer-original.svg", alt: "composer" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/codeigniter/codeigniter-plain.svg", alt: "codeigniter" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg", alt: "html5" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg", alt: "css" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original-wordmark.svg", alt: "tailwindcss" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/bootstrap/bootstrap-original.svg", alt: "bootstrap" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg", alt: "javascript" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg", alt: "vuejs" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg", alt: "typescript" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg", alt: "postgresql" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg", alt: "mysql" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/redis/redis-original.svg", alt: "redis" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mongodb/mongodb-original.svg", alt: "mongodb" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vscode/vscode-original.svg", alt: "vscode" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cloudflare/cloudflare-original.svg", alt: "cloudflare" },    
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/ssh/ssh-original.svg", alt: "ssh" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg", alt: "git" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg", alt: "github" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/gitlab/gitlab-original.svg", alt: "gitlab" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/npm/npm-original-wordmark.svg", alt: "npm" },    
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/githubactions/githubactions-original.svg", alt: "cicd-github-actions" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/dart/dart-original.svg", alt: "dart" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/flutter/flutter-original.svg", alt: "flutter" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/bitbucket/bitbucket-original.svg", alt: "bitbucket-pipelines" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/canva/canva-original.svg", alt: "canva" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg", alt: "python" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg", alt: "docker" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/amazonwebservices/amazonwebservices-original-wordmark.svg", alt: "aws" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/googlecloud/googlecloud-original.svg", alt: "gcp" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/androidstudio/androidstudio-original.svg", alt: "androidstudio" },
    { src: "https://cdn.jsdelivr.net/gh/devicons/devicon/icons/kotlin/kotlin-original.svg", alt: "kotlin" },
    { src: "https://cdn.simpleicons.org/openai/412991", alt: "openai" },
    { src: "https://cdn.simpleicons.org/googlegemini/8E75ED", alt: "gemini" },
    { src: "https://cdn.simpleicons.org/anthropic/D97757", alt: "claude" },
    { src: "https://cdn.simpleicons.org/meta/0668E1", alt: "llama-meta" },
    { src: "https://cdn.simpleicons.org/nvidia/76B900", alt: "nvidia" }
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Painel de Controle</h2>
                <a href="https://github.com/letsmg/erp-vue" target="_blank" class="flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg" class="w-5 h-5" />
                    Repositório do Projeto
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                            🌐 Infraestrutura & Deploy
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li><strong>Hospedagem:</strong> Site blindado na Oracle Cloud com proteção Cloudflare</li>
                            <li><strong>Acesso:</strong> <a href="https://check-host.net/ip-info?host=hierarca.com" target="_blank" class="text-indigo-600 hover:underline font-mono">Verificar IP hierarca.com</a></li>
                            <li><strong>CI/CD:</strong> Pipeline automatizado com testes <span class="text-indigo-600 font-semibold">PHPUnit</span> revisados ✅</li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                            🛡️ Segurança e Arquitetura
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li><strong>Auth:</strong> Algoritmo de hash <span class="bg-slate-100 px-1 rounded font-mono">Argon2id</span>.</li>
                            <li><strong>Tipagem Forte:</strong> Implementação de <span class="bg-slate-100 px-1 rounded">PHP Enums</span>.</li>
                            <li><strong>Qualidade:</strong> Código revisado seguindo padrões PSR.</li>
                        </ul>
                    </div>
                </div>

                <section id="sobre" class="py-20 bg-slate-50 border-t border-slate-200 scroll-mt-20 rounded-xl overflow-hidden shadow-inner">
                    <div class="max-w-6xl mx-auto px-6">
                        <div class="grid md:grid-cols-2 gap-16 items-start">
                            
                            <div class="space-y-8">
                                <div>
                                    <h2 class="text-5xl font-black text-slate-900 mb-2 uppercase italic tracking-tighter">
                                        {{ dadosPessoais.nome }}
                                    </h2>
                                    <div class="flex flex-wrap items-center gap-4 mb-6">
                                        <p class="text-indigo-600 font-bold tracking-widest uppercase text-sm">
                                            {{ dadosPessoais.cargo }}
                                        </p>
                                        <span class="text-slate-300 hidden md:block">|</span>
                                        <div class="flex gap-3">
                                            <a href="https://github.com/letsmg" target="_blank" class="text-slate-400 hover:text-slate-900 transition-colors">
                                                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg" class="h-5 w-5" alt="GitHub">
                                            </a>
                                            <a href="https://www.linkedin.com/in/letsmg/" target="_blank" class="text-slate-400 hover:text-indigo-600 transition-colors">
                                                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linkedin/linkedin-original.svg" class="h-5 w-5" alt="LinkedIn">
                                            </a>
                                        </div>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed text-lg">
                                        Atuando diretamente de <strong>{{ dadosPessoais.local }}</strong>, 
                                        desenvolvo soluções escaláveis utilizando o que há de mais moderno no ecossistema PHP. 
                                        Especialista em resolver problemas através de automações e ferramentas personalizadas.
                                    </p>
                                </div>

                                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8">
                                    <div class="flex-shrink-0">
                                        <div class="w-32 h-32 rounded-full border-4 border-white shadow-xl overflow-hidden bg-slate-200 relative group">
                                            <img src="https://github.com/letsmg.png" alt="Luiz Eduardo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            <div class="absolute inset-0 bg-indigo-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 w-full sm:w-auto">
                                        <a :href="dadosPessoais.linkWhats" target="_blank" 
                                            class="flex items-center gap-4 p-3 pr-8 bg-white border border-slate-200 rounded-2xl hover:border-green-500 transition-all group shadow-sm">
                                            <div class="w-10 h-10 bg-green-500 text-white rounded-xl flex items-center justify-center font-black text-lg">
                                                W
                                            </div>
                                            <div class="text-left font-bold">
                                                <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-none">WhatsApp</span>
                                                <span class="text-slate-900 group-hover:text-green-600 transition text-sm">{{ dadosPessoais.whatsapp }}</span>
                                            </div>
                                        </a>

                                        <div class="flex gap-3">
                                            <a href="https://www.linkedin.com/in/letsmg/" target="_blank" 
                                                class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-2xl hover:border-blue-600 transition-all group shadow-sm flex-1 sm:flex-none">
                                                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linkedin/linkedin-original.svg" class="h-5 w-5" alt="LinkedIn">
                                                <span class="text-slate-700 font-bold text-xs group-hover:text-blue-600">LinkedIn</span>
                                            </a>
                                            <a href="https://github.com/letsmg" target="_blank" 
                                                class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-2xl hover:border-slate-900 transition-all group shadow-sm flex-1 sm:flex-none">
                                                <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg" class="h-5 w-5" alt="GitHub">
                                                <span class="text-slate-700 font-bold text-xs group-hover:text-slate-900">GitHub</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                                <h3 class="text-slate-900 font-bold mb-8 uppercase tracking-widest text-xs flex items-center gap-3">
                                    <span class="w-2 h-2 bg-indigo-600 rounded-full animate-ping"></span>
                                    Tecnologias & Ferramentas
                                </h3>
                                
                                <div class="flex flex-wrap gap-4 justify-start transition-all duration-700">
                                    <div v-for="icon in techIcons" :key="icon.alt" class="relative group">
                                        <img 
                                            :src="icon.src" 
                                            :alt="icon.alt"
                                            :title="icon.alt.toUpperCase()"
                                            class="h-10 w-10 hover:scale-125 transition-transform drop-shadow-sm p-1 cursor-pointer"
                                            :class="{ 'bg-slate-900 rounded-lg shadow-md !p-1.5': icon.alt === 'tailwindcss' }" 
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>