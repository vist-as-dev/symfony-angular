import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';

import {HomePageComponent} from './page/home/home.component';

const routes: Routes = [
    {
        path: '',
        component: HomePageComponent,
        data: {
            title: 'Home',
        },
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule],
})
export class HomeRouting {
}
