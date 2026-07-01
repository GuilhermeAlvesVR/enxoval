FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /app
COPY Enxoval.Web/Enxoval.Web.csproj Enxoval.Web/
RUN dotnet restore Enxoval.Web/Enxoval.Web.csproj
COPY Enxoval.Web/ Enxoval.Web/
RUN dotnet publish Enxoval.Web/Enxoval.Web.csproj -c Release -o out

FROM mcr.microsoft.com/dotnet/aspnet:8.0
WORKDIR /app
COPY --from=build /app/out .
EXPOSE 5000
ENV ASPNETCORE_URLS=http://+:${PORT:-5000}
ENV DOTNET_SYSTEM_GLOBALIZATION_INVARIANT=1
ENTRYPOINT ["dotnet", "Enxoval.Web.dll"]
