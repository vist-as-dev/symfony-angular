import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';

import {HomeRouting} from './home.route';
import {MODULE_HOME_PAGE_HOME} from './page/home';

@NgModule({
    declarations: [
        ...MODULE_HOME_PAGE_HOME,
    ],
    imports: [
        CommonModule,

        HomeRouting,
    ],
})
export class HomeModule {
}
