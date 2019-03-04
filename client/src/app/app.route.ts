import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {FullLayoutComponent} from "./layout/full/full.component";

export const routes: Routes = [
    {
        path: '',
        component: FullLayoutComponent,
        data: {
            title: 'Home'
        },
        canActivate: [],
        children: [
            {
                path: '',
                loadChildren: './modules/home/home.module#HomeModule'
            },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forRoot(routes, {useHash: false})],
    exports: [RouterModule]
})
export class AppRouting {
}
