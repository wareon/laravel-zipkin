namespace php Wareon.Zipkin.Thrift.Demo

// 定义配置接口  thrift -r -out ./ --gen php:server service_demo.thrift

service Demo {
    string getStatus()
}
