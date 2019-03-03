import {BrowserModule} from '@angular/platform-browser';
import {NgModule} from '@angular/core';
import {NoopAnimationsModule} from '@angular/platform-browser/animations';

import {AppComponent} from './app.component';

import {CoreModule} from './core/core.module';
import {AppRouting} from './app.route';

import {APP_SIMPLE_LAYOUT, APP_FULL_LAYOUT} from './layout';

@NgModule({
    declarations: [
        AppComponent,
        ...APP_SIMPLE_LAYOUT,
        ...APP_FULL_LAYOUT,
    ],
    imports: [
        BrowserModule,
        NoopAnimationsModule,

        AppRouting,
        CoreModule,
    ],
    providers: [],
    bootstrap: [AppComponent],
})
export class AppModule {
}
