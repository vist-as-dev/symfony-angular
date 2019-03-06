import {NgModule, Optional, SkipSelf} from '@angular/core';
import {CommonModule} from '@angular/common';

import {SecurityModule} from "./security/security.module";
import {LayoutModule} from "./layout/layout.module";

@NgModule({
    declarations: [],
    imports: [
        CommonModule,

        SecurityModule,
        LayoutModule,
    ],
    exports: [
        SecurityModule,
        LayoutModule,
    ]
})
export class CoreModule {
    constructor(@Optional() @SkipSelf() parentModule: CoreModule) {
        if (parentModule) {
            throw new Error(
                `${parentModule.constructor.name} has already been loaded. Import this module in the AppModule only.`
            );
        }
    }
}
