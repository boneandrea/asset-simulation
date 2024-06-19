# hoge

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur) + [TypeScript Vue Plugin (Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin).

## Customize configuration

See [Vite Configuration Reference](https://vitejs.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

## Tips
### Use bootstrap icons

```
npm install --save bootstrap bootstrap-icons
```

main.js
```
import 'bootstrap'
import '@/scss/custom.scss' # create this
```
bootstrapの変数を上書きしてからbootstrapのscssをimportすると反映される。

scss/custom.scss
```
$body-bg: #000;
@import "node_modules/bootstrap/scss/bootstrap";
```

bootstrap iconsを利用する
```
main.js
import 'bootstrap-icons/font/bootstrap-icons.css'
```
