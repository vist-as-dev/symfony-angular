import {NgModule, Optional, SkipSelf} from '@angular/core';
import {CommonModule} from '@angular/common';

import {SingletonGuard} from './guard/singleton.guard';
import {SecurityModule} from "./security/security.module";
import {LocaleModule} from "./locale/locale.module";
import {UiModule} from "./ui/ui.module";

@NgModule({
    declarations: [],
    imports: [
        CommonModule,

        SecurityModule,
        LocaleModule,
        UiModule,
    ],
    exports: [
        SecurityModule,
        LocaleModule,
        UiModule,
    ]
})
export class CoreModule extends SingletonGuard { // Ensure that CoreModule is only loaded into AppModule
    // Looks for the module in the parent injector to see if it's already been loaded (only want it loaded once)
    constructor(@Optional() @SkipSelf() parentModule: CoreModule) {
        super(parentModule);
    }
}
