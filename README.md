# What

simulator



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
