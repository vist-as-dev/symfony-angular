import {NgModule} from '@angular/core';
import {CommonModule} from "@angular/common";
import {RouterModule} from "@angular/router";
import {FlexLayoutModule} from "@angular/flex-layout";

import {APP_FULL_LAYOUT, FullLayoutComponent} from "./full";
import {APP_SIMPLE_LAYOUT, SimpleLayoutComponent} from "./simple";

import {LocaleModule} from "./locale.module";
import {AngularMaterialModule} from "./angular-material.module";

@NgModule({
    imports: [
        CommonModule,
        RouterModule,

        FlexLayoutModule,
        AngularMaterialModule,

        LocaleModule,
    ],
    declarations: [
        ...APP_FULL_LAYOUT,
        ...APP_SIMPLE_LAYOUT,
    ],
    exports: [
        FullLayoutComponent,
        SimpleLayoutComponent,

        FlexLayoutModule,
        AngularMaterialModule,

        LocaleModule,
    ],
})
export class LayoutModule {
}