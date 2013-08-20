所有的文档标记都是在每一行的 * 后面以@开头。如果在一段话的中间出来@的标记，这个标记将会被当做普通内容而被忽略掉。
@access        该标记用于指明关键字的存取权限：private、public或proteced 使用范围：class,function,var,define,module
@author        指明作者
@copyright    指明版权信息
@const        使用范围：define 用来指明php中define的常量
@final            使用范围：class,function,var 指明关键字是一个最终的类、方法、属性，禁止派生、修改。
@global        指明在此函数中引用的全局变量
@name            为关键字指定一个别名。
@package    用于逻辑上将一个或几个关键字分到一组。
@abstrcut    说明当前类是一个抽象类
@param        指明一个函数的参数
@return        指明一个方法或函数的返回值
@static            指明关建字是静态的。
@var            指明变量类型
@version        指明版本信息
@todo            指明应该改进或没有实现的地方
@link            可以通过link指到文档中的任何一个关键字
@ingore        用于在文档中忽略指定的关键字
@power		  指明函数的权限定义 第一个参数为key第二个为说明