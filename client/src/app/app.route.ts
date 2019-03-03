import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {SimpleLayoutComponent} from './layout/simple/simple.component';

export const routes: Routes = [
    {
        path: '',
        component: SimpleLayoutComponent,
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
